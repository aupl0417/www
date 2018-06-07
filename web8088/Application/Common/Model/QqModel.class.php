<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class QqModel extends CommonModel {

    
    
    
    /**
     * 插入QQ登录的信息，
     * $access_token
     * $refresh_token
     * $qqopenid
     * $name
     * $avatar
     * @param unknown $access_token
     * @param unknown $refresh_token
     * @param unknown $qqopenid
     * @param unknown $name
     * @param unknown $avatar
     * @return boolean|unknown
     */
    public function addqqUser($qqopenid,$user_info){

        header("content-Type: text/html; charset=Utf-8");//设置字符编码
        
        
        if ($user_info['figureurl_qq_2']==''){
            $avatarpath=GrabImage($user_info['figureurl_qq_1']);
        }else{
            $avatarpath=GrabImage($user_info['figureurl_qq_2']);
        }
        $qq_avatarpath='.'.$avatarpath;
        $cut_qqavatar=$this->imagecut($qq_avatarpath);//裁剪qq的头像
        if ($cut_qqavatar===false){
//             return false;
            return $cut_qqavatar;
        }
        $avatarpath=$cut_qqavatar;
        
        if ($user_info['gender']=='男'){
            $sex=1;
        }else {
            $sex=0;
        }
        
        
        $User=M('User');
        $userdata['avatar']=$avatarpath;
        $userdata['lastname']=$user_info['nickname'];
        $userdata['year']=$user_info['year'];
        $userdata['sex']=$sex;
        $userdata['ctime']=current_datetime();
        $userId=$User->data($userdata)->filter('strip_tags')->add();//QQ登录第一次时为此QQ用户创建本站的用户账号信息
        if (!$userId){
//             return false;
            return $User->getDbError();
        }
        
        $create['access_token']=$_SESSION['access_token'];
        $create['expires_in']=$_SESSION['expires_in'];
        $create['refresh_token']=$_SESSION['refresh_token'];
        $create['openid']=$qqopenid;
        $create['name']=$user_info['nickname'];
        $rules = array(
        );
        $auto = array (
        );
        $addqq=$this->validate($rules)->auto($auto)->create($create);//创建第一次QQ登录的信息对象
        if (!$addqq){
//             return false;
            return $this->getError();
        }
        $data['access_token']=$this->access_token;
        $data['expires_in']=$this->expires_in;
        $data['refresh_token']=$this->refresh_token;
        $data['openid']=$this->openid;
        $data['name']=$user_info['nickname'];
        $data['uid']=$userId;
        $data['update_time']=time();
        $qqid=$this->data($data)->filter('strip_tags')->add();//保存第一次QQ登录获取的信息
        
        if (!$qqid){
//             return false;
            return $this->getDbError();
        }
        

        // 登陆成功，设置thirduser的二维数组
		session('thirduser.id',$userId);  						        //设置session
		session('thirduser.name',$user_info['nickname']);  	            //设置session
		session('thirduser.avatar',$avatarpath);  					    //设置session
		

// 		session('user.id',$userId);  						        //设置session
// 		session('user.name',$user_info['nickname']);  	            //设置session
// 		session('user.avatar',$avatarpath);  					    //设置session
		session('shopkeeper',null);
		session('shop_auto_login',null);

// 		return $userId;
        return true;
    }
    
    
    /**
     * 自动续期后更新数据
     * @param unknown $qqopenid
     * @return boolean
     */
    public function updataToken($qqopenid=''){
        $updata=array(
            'access_token'  =>  $_SESSION['access_token'],
            'expires_in'  =>  $_SESSION['expires_in'],
            'update_time'  =>  time(),
            'refresh_token'  =>  $_SESSION['refresh_token'],
        );
        $rell=$this->where("openid='%s'",$qqopenid)->setField($updata);
        if (!$rell){
            return false;
        }
        return true;
    }
    
    /**
     * 检查某个openid是否登录过，登录过就返回该qq的相关信息，没登录过的话就返回flase
     * @param string $openid
     * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function checkopenid($openid=''){
//         $rel=$this->where("openid='".$openid."'")->find();
        $rel=$this->where("openid='%s'",$openid)->find();
        if (!$rel){
            return false;
        }
        return $rel;
    }
    
    

    /**
     * 裁剪图片75*75，$path是完整的路径，带.的，如./xx/xxx.jpg
     * @param string $path
     * @return \Think\Image|boolean
     */
    public function imagecut($path=''){
        $user_avatar=C('user_avatar');
        $pathname=date("Y/m/d");
        $imgname=uniqid();
        $thumb='.'.$user_avatar.'/qq/'.$pathname.'/'.$imgname.'.jpg';
        
        $thumbimg='.'.$user_avatar.'/qq/'.$pathname;
        $this->mk_dir($thumbimg);
        
        $image = new \Think\Image();
        $image->open($path);
        // 生成一个固定大小为75*75的缩略图并保存为thumb.jpg
        $imageInfo=$image->thumb(75, 75,\Think\Image::IMAGE_THUMB_FIXED)->save($thumb);
        if (!$imageInfo){
            return false;
        }
        $unimagestatus=unlink($path);		//删除//
        $thumbdeldian=$user_avatar.'/qq/'.$pathname.'/'.$imgname.'.jpg';
        return $thumbdeldian;
    }

    
    // 循环创建目录
    public function mk_dir($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir,$mode)){
            return true;
        } 
        if (!$this->mk_dir(dirname($dir),$mode)){
            return false;
        } 
        return @mkdir($dir,$mode);
    }
    
}