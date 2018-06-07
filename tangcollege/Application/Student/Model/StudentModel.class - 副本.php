<?php

namespace Student\Model;
use Think\Model;



class StudentModel extends Model {

    protected $_validate = array(
        array('nickname', '1,16', '昵称长度为1-16个字符', self::EXISTS_VALIDATE, 'length'),
        array('nickname', '', '昵称被占用', self::EXISTS_VALIDATE, 'unique'), //用户名被占用
		
    );
    
    protected $_auto = array(
        array('reg_time', getTime, self::MODEL_INSERT, 'callback'),
        array('last_login_time', getTime, self::MODEL_BOTH, 'callback'),
        array('last_login_ip', getIp, self::MODEL_BOTH, 'callback'),
        array('reg_ip', getIp, self::MODEL_INSERT, 'callback'),
    );
    
    public function getTime(){
        return time();
    }
    
    public function getIp(){
        return get_client_ip(1);//返回地址数字
    }
    
    public function lists($status = 1, $order = 'stu_userId DESC', $field = true){
        $map = array('status' => $status);
        return $this->field($field)->where($map)->order($order)->select();
    }

    /**
     * 登录指定用户
     * @param  integer $stu_userId 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($stu_userId){
        /* 检测是否在当前应用注册 */
        $user = $this->field(true)->where(array('stu_userId'=>$stu_userId))->find();
        if(!$user) {
            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
            return false;
        }

        //记录行为
        action_log('user_login', 'member', $stu_userId, $stu_userId);

        /* 登录用户 */
        $this->autoLogin($user);
        return true;
    }

    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
    }

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
            'stu_userId'             => $user['stu_userId'],
            'login'           => array('exp', '`login`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $this->save($data);

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid'             => $user['stu_userId'],
            'username'        => $user['nickname'],
			'branchId'        => $user['branchId'],
			'identityType'    => $user['identityType'],
            'last_login_time' => $user['last_login_time'],
        );

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));

    }

    public function getNickName($stu_userId){
        return $this->where(array('stu_userId'=>(int)$stu_userId))->getField('nickname');
    }

}
