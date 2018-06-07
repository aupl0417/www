<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class SinaModel extends CommonModel {
    


    /**
     * 插入 sina  登录的信息，
     * $access_token
     * $expire_in
     * $userId
     * $avatar
     * @param unknown $access_token
     * @param unknown $refresh_token
     * @param unknown $qqopenid
     * @param unknown $name
     * @param unknown $avatar
     * @return boolean|unknown
     */
    public function addsinaUser($userId,$user_info){
    
    
        
        if ($user_info['avatar_large']!=''){
            $avatarpath=GrabImage($user_info['avatar_large']);//获取180像素的头像
        }else{
            $avatarpath=GrabImage($user_info['profile_image_url']);//获取50像素的头像
        }
        $sina_avatarpath='.'.$avatarpath;
        $cut_sinaavatar=$this->imagecut($sina_avatarpath);//裁剪sina的头像
        if ($cut_sinaavatar===false){
            return false;
        }
        $avatarpath=$cut_sinaavatar;
        
        
        if ($user_info['gender ']=='m'){
            $sex=1;
        }else{
            $sex=0;
        }
        
        $User=M('User');
        $userdata['avatar']=$avatarpath;
        $userdata['lastname']=$user_info['screen_name'];
        $userdata['sex']=$sex;
        $userdata['remark']=$user_info['description'];
        $userdata['ctime']=current_datetime();
        $userSinaId=$User->data($userdata)->add();//创建本站的user————id
        if (!$userSinaId){
            return false;
        }
        $create['access_token']=$_SESSION['access_token'];
        $create['expires_in']=$_SESSION['expires_in'];
        $create['sina_uid']=$userId;
        $create['uid']=$userSinaId;
        $rules = array(
        );
        $auto = array (
        );
        $addsina=$this->validate($rules)->auto($auto)->create($create);
        if (!$addsina){
            return false;
        }
        $data['access_token']=$this->access_token;
        $data['expires_in']=$this->expires_in;
        $data['sina_uid']=$this->sina_uid;
        $data['uid']=$userSinaId;
        $data['updata_time']=time();
        $sinaid=$this->data($data)->add();
        if (!$sinaid){
            return false;
        }
    

        // 登陆成功，设置thirduser的二维数组
        session('thirduser.id',$userSinaId);  						        //设置session
        session('thirduser.name',$user_info['screen_name']);  	            //设置session
        session('thirduser.remark',$user_info['description']);  					    //设置session设置session
        session('thirduser.sex',$sex);  					    //设置session设置session
        session('thirduser.avatar',$avatarpath);  					    //设置session
        
        
//         session('user.id',$userSinaId);  						        //设置session
//         session('user.name',$user_info['screen_name']);  	            //设置session
//         session('user.remark',$user_info['description']);  					    //设置session设置session
//         session('user.sex',$sex);  					    //设置session设置session
//         session('user.avatar',$avatarpath);  					    //设置session
        session('shopkeeper',null);
		session('shop_auto_login',null);
    
        return $userId;
    }
    
    
    /**
     * 检查某个sina_uid是否登录过，登录过就返回该qq的相关信息，没登录过的话就返回flase
     * @param string $sinauid
     * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function checksinauid($sinauid=0){
        $rel=$this->where("sina_uid=%d",$sinauid)->find();
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
        $thumb='.'.$user_avatar.'/sina/'.$pathname.'/'.$imgname.'.jpg';
    
        $thumbimg='.'.$user_avatar.'/sina/'.$pathname;
        $this->mk_dir($thumbimg);// 循环创建目录
    
        $image = new \Think\Image();
        $image->open($path);
        // 生成一个固定大小为75*75的缩略图并保存为thumb.jpg
        $imageInfo=$image->thumb(75, 75,\Think\Image::IMAGE_THUMB_FIXED)->save($thumb);
        if (!$imageInfo){
            return false;
        }
        $unimagestatus=unlink($path);		//删除//
        $thumbdeldian=$user_avatar.'/sina/'.$pathname.'/'.$imgname.'.jpg';
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