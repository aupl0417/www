<?php

namespace Student\Controller;
use User\Api\UserApi;


class PublicController extends \Think\Controller {

    /**
     * 后台用户登录
     * 
     */
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
            /* 检测验证码 TODO: */
            if(!check_verify($verify)){
                //$this->error('验证码输入错误！');
            }

            /* 调用UC登录接口登录 */
            $User = new UserApi;
            $uid = $User->login($username, $password,0);
			if($uid == -1) {
				$uid = $this->searchUserToThirdParty($username, $password);
			}
            if(0 < $uid){ //UC登录成功
                /* 登录用户 */
                $Student = D('Student');
                if($Student->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    $this->success('登录成功！', U('Index/index'));
                } else {
                    $this->error($Student->getError());
                }

            } else { //登录失败
                switch($uid) {
                    case -2: $error = '密码错误！'; break;
					case -3: $error = '用户不存在！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        } else {
            if(is_login(0)){
                $this->redirect('Index/index');
            }else{
                /* 读取数据库中的配置 */
                $config	=	S('DB_CONFIG_DATA');
                if(!$config){
                    $config	=	D('Config')->lists();
                    S('DB_CONFIG_DATA',$config);
                }
                C($config); //添加配置
                $this->display(__FUNCTION__);
            }
        }
    }
	
    //如果本系统无此用户 去第三方系统寻找
    protected function searchUserToThirdParty($username, $password) {
       //定义一个要发送的目标URL；
       $url = C('ERP_OPEN_API_URL').'/mallapi/login.json';
       //定义传递的参数数组；
       $data['username']=$username;
       $data['password']=getSuperMD5($password);
	   $data['parterId']=C('PARTERID');
	   ksort($data);
	   $secretkey = C('ERP_ECRET_KEY');
	   $data['signValue'] =  md5(http_build_query($data)."&".$secretkey);
       //定义返回值接收变量；
       $httpstr = http($url, $data, 'GET', array("Content-type: text/html; charset=utf-8"));
	   if(empty($httpstr))
	     return false;
	   $result = json_decode($httpstr,true);
	   if(!is_array($result)) {
		   return false;
	   }else{
		   if($result['id']!='1001'){
		     return false;
	       }else{
			   $info = $result['info'];
			   $studentModel = new \Common\Model\StudentModel();
			   $data['username'] = $username;
			   $data['password'] = $data['password'];
			   $data['reg_time'] = time();
			   $data['reg_ip'] = get_client_ip(1);
			   $data['identityType'] = 0;
			   $data['thirdPartyUserId'] = $info['u_id'];
			   $data['mobile'] = !empty($info['u_tel']) ? $info['u_tel'] : null;
			   $data['email'] = !empty($info['u_email']) ? $info['u_email'] : null; 
			   $uid = $studentModel->addInfoCacheClean($data);
			   if(!$uid){
					return -3; 
                } else {
                    return $uid;
                }
		   }
	   }
	}

    


    /* 退出登录 */
    public function logout(){
        if(is_login()){
            D('Student')->logout();
            session('[destroy]');
            $this->success('退出成功！', U('login'));
        } else {
            $this->redirect('login');
        }
    }

    public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }

}
