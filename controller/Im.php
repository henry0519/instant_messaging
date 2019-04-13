<?php
namespace app\im\controller;
use think\Controller;
use think\facade\Request;
use think\Db;
use think\cache\driver\Redis;
class Im extends Controller{
    private $base_url = 'https://xxx.cn';//图片前缀
    //Url=HOST/im
    public function imByUser(){
        $request = Request::param();
        //Token-转换-用户id
        if(!isset($request['uid']) || !isset($request['lawyerid'])){
            $this->redirect('/errorpage?msg=参数错误');
        }
        $uid = $request['uid'];
        $lawyerid = $request['lawyerid'];
        if($uid == $lawyerid){
            $this->redirect('/errorpage?msg=不可以自己聊聊天');
        }
        //判断律师是否存在
        $getlawyerinfo = model('lawyer')->where('uid',$lawyerid)->field('id,name')->find();
        if(!$getlawyerinfo){
            $this->redirect('/errorpage?msg=律师未入驻');
        }
        $title = $getlawyerinfo['name'];
        $getuserinfo = $user = model('member')->where('id',$uid)->field('id,user_name,nick_name,avatar')->find();
        if(!$getuserinfo){
            $this->redirect('/errorpage?msg=用户不存在');
        }
        //律师不能访问用户端
        $islawyer = model('lawyer')->where('uid',$uid)->field('id,name')->find();
        if($islawyer){
            $this->redirect('/errorpage?msg=您是律师不允许使用用户端聊天');
        }
        //
        $user['username'] = ($getuserinfo['nick_name'])?$getuserinfo['nick_name']:$getuserinfo['user_name'];
        $user['avatar'] = ($getuserinfo['avatar'])?$getuserinfo['avatar']:'/static/layim/images/default_head.jpg';
        $user['status'] = 'online';
        unset($getuserinfo['nick_name']);
        unset($getuserinfo['user_name']);
        $redis = $this->redis_connect();
        $Huser = $redis->HEXISTS('User','user_'.$uid);
        
        //if(!$Huser){
        $redis->HSET('User','user_'.$uid,json_encode($user)); 
        //}
        $Uinfo = $redis->HGET('User','user_'.$uid);
        $userinfo = json_decode($Uinfo,true);
        $userinfo['avatar'] = $this->base_url.$userinfo['avatar'];
        $this->assign('info',array('uid'=>$uid,'lawyerid'=>$lawyerid,'userinfo'=>$userinfo,'title'=>$title));
        return $this->fetch('user');
    }
    //Url=HOST/ims
    public function imByLawyer(){
        $request = Request::param();
        file_put_contents("UUUU.json","Log:".json_encode($request).PHP_EOL, FILE_APPEND);
        
        //$token = "";
        if(!isset($request['lawyerid'])){
            $this->redirect('/errorpage?msg=参数错误');
        }
        $lawyerid = $request['lawyerid'];
        
        //判断律师是否存在
        $getlawyerinfo = model('lawyer')->where('uid',$lawyerid)->field('uid,name,headimg')->find();
        if(!$getlawyerinfo){
            $this->redirect('/errorpage?msg=律师未入驻');
        }
        file_put_contents("UUUU.json","Log:-Time:".date('Y-m-d H:i:s',time()).'---Login'.microtime().PHP_EOL, FILE_APPEND);
        $title = '我的主板';
        $userlist = '';
        $redis = $this->redis_connect();
        
        $Hlawyer = $redis->HEXISTS('Lawyer',$lawyerid.'_lawyer');
        
        //if(!$Hlawyer){
            $lawyerinfo = array(
                'id' => $getlawyerinfo['uid'],
                'username' => $getlawyerinfo['name'],
                'avatar' =>($getlawyerinfo['headimg']!='')?$getlawyerinfo['headimg']:'/static/layim/images/default_head.jpg'
            );
            $redis->HSET('Lawyer',$lawyerid.'_lawyer',json_encode($lawyerinfo));
        //}
        //die;
        $Linfo = $redis->HGET('Lawyer',$lawyerid.'_lawyer');
        $userinfo = json_decode($Linfo,true);
        $userinfo['status'] = 'online';
        $redis->HSET('Lawyer',$lawyerid.'_lawyer',json_encode($userinfo));
        
        $userinfo['avatar'] = $this->base_url.$userinfo['avatar'];
        $Huserlist = $redis->HGETALL('User');
        $userlist = [];
        foreach ($Huserlist as &$val) {
            $uinfo = json_decode($val,true);
            $uinfo['avatar'] = $this->base_url.$uinfo['avatar'];
            $userlist[] = $uinfo;
        }
        
        $userlist = json_encode($userlist);
        $this->assign('info',array("lawyerid"=>$lawyerid,'userinfo'=>$userinfo,'userlist'=>$userlist,'title'=>$title));
        return $this->fetch('lawyer');
    }
    public function errorPage(){
        $data = isset($_GET['msg'])?$_GET['msg']:'page error!';
        echo "<style>h1{margin:0 auto;font-size:24px;}</style>";
        echo "<h1>{$data}<h1>";
    }
    private function redis_connect(){
        $redis=new \Redis();
        $redis->connect("IP","6379"); //redis ip  端口
        $redis->auth("password");//redis密码
        $redis->select(1);
        return $redis;
    }   
}