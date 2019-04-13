<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;



use App\Utility\Pool\MysqlPool;
use EasySwoole\Component\Pool\PoolManager;


/**
 * Class Index
 * @package App\HttpController
 */
class Index extends Controller
{
    /**
     * 首页方法
     * @author : evalor <master@evalor.cn>
     */
    public function index()
    {
        $result = json_encode(array('status'=>202,'msg'=>'接口地址错误！'));
        $this->response()->withHeader('Content-type', 'text/html;charset=utf-8');
        $this->response()->write($result);
        
        /*
        $this->response()->withHeader('Content-type', 'text/html;charset=utf-8');
        $this->response()->write('<div style="text-align: center;margin-top: 30px"><h2>欢迎使用EASYSWOOLE</h2></div></br>');
        $this->response()->write('<div style="text-align: center">您现在看到的页面是默认的 Index 控制器的输出</div></br>');
        $this->response()->write('<div style="text-align: center"><a href="https://www.easyswoole.com/Manual/2.x/Cn/_book/Base/http_controller.html">查看手册了解详细使用方法</a></div></br>');
        */
    }
    
    public function index2(){
        
        $mysql = PoolManager::getInstance()->getPool(MysqlPool::class)->getObj();
        $table = 'yt_im_room';
        
        //$list = $mysql->get($table);
        $id = '21_1';
        $mysql ->where('uid_lawyerid',$id,'=');
        $list = $mysql->getOne($table);
        if($list){
            $res = 1;
            $insert_id = $list['id'];
        }else{
            $res = 0;
            //
            $data = array(
                'uid_lawyerid'=>$id,
                'addtime'=>time(),
                'lastime'=>time()
                );
            $insert_id = $mysql->insert($table, $data);  
            
        }
        
        $this->response()->write("id:".$insert_id.'<br />');
        
        $this->response()->write("res:".$res.'<br />');
        
        //$this->response()->write('count:'.count($list).'<br />');
        $this->response()->write(json_encode($list));
        
        PoolManager::getInstance()->getPool(MysqlPool::class)->recycleObj($mysql);
        
        
        
        
        
        /*
        
        $result = json_encode(array('status'=>200,'msg'=>'ok！','db'=>$db));
        $this->response()->withHeader('Content-type', 'text/html;charset=utf-8');
        $this->response()->write($result);
        
        */
    }
}
