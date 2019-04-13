<?php
namespace app\im\controller;
use think\Controller;
use think\facade\Request;
use think\Db;
use think\cache\driver\Redis;
class Imindex extends Controller{
    private $base_url = 'https://xxx.cn';//图片前缀
    public function getchatlog(){
        if (Request::isPost()) {
            $request = Request::param();
            $uidlawyerid = $request['data'];
            $mine = $request['mine'];
            
            $room = model('imroom')->where(['uid_lawyerid'=>$uidlawyerid])->find();
            if(!$room){
                $html = '<div  class="layim-chat-main" style="">聊天室被删除!<a href="javascript:location.reload();" style="color:#a90005;font-size:16px;"><< 返回</a></div>';
                return json(array('status'=>201,'msg'=>'聊天室被删除','data'=>array('html'=>$html)));
            }
            $chatlist = model('imchatlog')->where('room_id',$room['id'])->order('id desc')->select();
            if(count($chatlist)){
                $html = '<div  class="layim-chat-main">聊天记录空!<a href="javascript:location.reload();" style="color:#a90005;font-size:16px;"><< 返回</a></div>';
                return json(array('status'=>202,'msg'=>'聊天记录空','data'=>array('html'=>$html)));
            }
            $redis = $this->redis();
            $html = '';
            $html .= '<div class="layui-unselect layim-content"><div class="layim-chat layim-chat-friend"><div class="layim-chat-main" style="bottom:0"><div class="layim-chat-system"><ul>';
            for($i=0;$i<count($chatlist);$i++){
                $lawyer = $redis->HEXISTS('Lawyer',$chatlist[$i]['from_uid'].'_lawyer');
                if($lawyer){
                    //From---Lawyer
                    $table = 'Lawyer';$column = $chatlist[$i]['from_uid'].'_lawyer';
                }else{
                    //From---User
                    $table = 'User';$column = 'user_'.$chatlist[$i]['from_uid'];
                }
                $info = $redis->HGET($table,$column);
                $info = json_decode($info,true);
                $chatlist[$i]['from_avatar'] = $this->base_url.$info['avatar'];
                $chatlist[$i]['from_username'] = $info['username'];
                $chatlist[$i]['time'] = date('Y-m-d H:i:s',$chatlist[$i]['addtime']);
            }
            
            for($i=0;$i<count($chatlist);$i++){
                
                $html .='<li class="layim-chat-system"><span>'.date('Y-m-d H:i:s',$chatlist[$i]['addtime']).'</span></li>';
                
                if($chatlist[$i]['from_uid'] == $mine){
                    $html .= '<li class="layim-chat-li layim-chat-mine">';
                }else{
                    $html .= '<li class="layim-chat-li">';
                }
                $html .= '<div class="layim-chat-user">';
                $html .= '<img src="'.$chatlist[$i]['from_avatar'].'">';
                $html .= '<cite>'.$chatlist[$i]['from_username'].'</cite></div>';
                $html .= '<div class="layim-chat-text">'.$chatlist[$i]['content'].'</div>';
                $html .= '</li>';
            }
            $html .='<li class="layim-chat-system"><span style="background-color:#a90005">没有更多了！！！</span></li>';
            
            $html .= '</div></div></div></div></ul>';
            $result = array('status'=>200,'msg'=>'获取成功','data'=>array('chatlist'=>$chatlist,'html'=>$html));
        }else{
            $result = array('status'=>210,'msg'=>'请求方式错误');
        }    
        return json($result);
    }
    
    private function redis_connect(){
        $redis=new \Redis();
        $redis->connect("IP","6379"); //redis ip  端口
        $redis->auth("password");//redis密码
        $redis->select(1);
        return $redis;
    }   

}