<?php
namespace App\WebSocket;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Swoole\Task\TaskManager;
use EasySwoole\Socket\AbstractInterface\Controller;
#-2019-Redis-Mysql支持
use EasySwoole\Component\Pool\PoolManager;
use App\Utility\Pool\RedisPool;
use App\Utility\Pool\MysqlPool;

class LayIm extends Controller
{
    //绑定
    public function bind()
    {
        $Args = $this->caller()->getArgs();
        $redis = PoolManager::getInstance()->getPool(RedisPool::class)->getObj();
        $uid = $Args['uid'];
        $lawyerid = $Args['lawyerid'];
        if($lawyerid === 0){
            $chatid = 0;
            $chatinfo = 0;
            $Linfo = $redis->HGET('Lawyer',$uid.'_lawyer');
            $lawyer = json_decode($Linfo,true);
            $lawyer['status'] = 'online';
            $redis->HSET('Lawyer',$uid.'_lawyer',json_encode($lawyer)); 
        }else{
            $Uinfo = $redis->HGET('User','user_'.$uid);
            $user = json_decode($Uinfo,true);
            $user['status'] = 'online';
            $redis->HSET('User','user_'.$uid,json_encode($user)); 
            $lawyerinfo = $redis->HGET('Lawyer',$lawyerid.'_lawyer');
            if($lawyerinfo){
                $lawyerinfo = $chatinfo = json_decode($lawyerinfo,true);
                $lawyer = true;
                $lawyerinfo['name'] = $lawyerinfo['username'];
                $lawyerinfo['type'] = 'friend';
                unset($lawyerinfo['username']);
            }else{
                $lawyer = false;
            }
            $msg = array('action'=>'lawyer','lawyer'=>$lawyer,'content'=>$lawyerinfo);
            $this->response()->setMessage(json_encode($msg));
            $chatid = (isset($chatinfo['id']))?$chatinfo['id']:'';
        }
        $client_id = $this->caller()->getClient()->getFd();
        $exit_fd = $redis->HEXISTS('User-Client-relationship',$uid);
        if($exit_fd)
        $redis->HDEL('User-Client-relationship',$uid);
        $redis->HSET('User-Client-relationship',$uid,$client_id);
        
        $exit_uid = $redis->HEXISTS('Client-User-relationship',$client_id);
        if($exit_uid)
        $redis->HDEL('Client-User-relationship',$client_id);
        $redis->HSET('Client-User-relationship',$client_id,$uid);
        
        PoolManager::getInstance()->getPool(RedisPool::class)->recycleObj($redis);
        if($chatid>0){
            $message = ($chatinfo['status'] == 'online')?'在线':'离线';
            $this->isonline(['chatid'=>$chatid,'message'=>$message,'avatar'=>$chatinfo['avatar'],'status'=>$chatinfo['status']]);
        }
    }
    private function isonline($data){
        $client = $this->caller()->getClient();
        TaskManager::async(function () use ($client,$data){
            //sleep(1);
            $server = ServerManager::getInstance()->getSwooleServer();
            $system_msg = array('system'=>true,'id'=>$data['chatid'],'avatar'=>$data['avatar'],'status'=>$data['status'],'type'=>'friend','content'=>$data['message']);
            $msg = array('action'=>'chatMessage','content'=>$system_msg);
            $server->push($client->getFd(),json_encode($msg));
        });
    }
    public function chatMessage(){
        $Args = $this->caller()->getArgs();
        
        $redis = PoolManager::getInstance()->getPool(RedisPool::class)->getObj();
        //190
        $toid = $Args['to']['id'];
        $lawyer = $redis->HEXISTS('Lawyer',$toid.'_lawyer');
        $user = $redis->HEXISTS('User','user_'.$toid);
        if($lawyer){
            $table = 'Lawyer';
            $column = $toid.'_lawyer';
        }
        if($user){
            $table = 'User';
            $column = 'user_'.$toid;
        }
        $status = 'offline';
        if((isset($table) && isset($column))){
            $Uinfo = $redis->HGET($table,$column);
            $userinfo = json_decode($Uinfo,true);
            $status = $userinfo['status'];
        }
        //
        PoolManager::getInstance()->getPool(RedisPool::class)->recycleObj($redis);
        //if(isset($Args['to']['status']) && $Args['to']['status'] == 'offline'){
        if($status == 'offline'){
            $message = '对方离线';
            //$this->isonline(['chatid'=>$Args['to']['id'],'message'=>$message]);
            $this->isonline(['chatid'=>$Args['to']['id'],'message'=>$message,'avatar'=>$Args['to']['avatar'],'status'=>$status]);
        }else{
            $redis = PoolManager::getInstance()->getPool(RedisPool::class)->getObj();
            $client_id = $redis->HGET('User-Client-relationship',$Args['to']['id']);
            PoolManager::getInstance()->getPool(RedisPool::class)->recycleObj($redis);
            $Args['mine']['mine'] = false;
            $Args['mine']['type'] = 'friend';
            $msg = array('action'=>'chatMessage','content'=>$Args['mine']);
            $this->pushDataToUser($client_id,$msg);
            $this->saveToMysql($Args);
        }
    }
    private function saveToMysql($data){
        $mysql = PoolManager::getInstance()->getPool(MysqlPool::class)->getObj();
        $table = 'yt_im_room';
        $table_chat = 'yt_im_chatlog';
        
        $mineid = $data['mine']['id'];
        $redis = PoolManager::getInstance()->getPool(RedisPool::class)->getObj();
        $isuser = $redis->HEXISTS('User','user_'.$mineid);
        PoolManager::getInstance()->getPool(RedisPool::class)->recycleObj($redis);
        if($isuser){
            $uid_lawyerid = $mineid.'_'.$data['to']['id'];
        }else{
            $uid_lawyerid = $data['to']['id'].'_'.$mineid;
        }
        $mysql ->where('uid_lawyerid',$uid_lawyerid,'=');
        $room = $mysql->getOne($table);
        if($room){
            $roomid = $room['id'];
        }else{
            $roomdata = array(
                'uid_lawyerid'=>$uid_lawyerid,
                'addtime'=>time(),
                'lastime'=>time()
                );
            $roomid = $mysql->insert($table, $roomdata);
        }
        $chatdata = array(
            'room_id'=>$roomid,
            'from_uid'=>$data['mine']['id'],
            'to_uid'=>$data['to']['id'],
            'content'=>$data['mine']['content'],
            'addtime'=>time()
            );
        $mysql->insert($table_chat, $chatdata);
        
        PoolManager::getInstance()->getPool(MysqlPool::class)->recycleObj($mysql);
    }
    private function pushDataToUser($client_id,$message){
        $server = ServerManager::getInstance()->getSwooleServer();
        $server->push($client_id,json_encode($message));
    }
}
