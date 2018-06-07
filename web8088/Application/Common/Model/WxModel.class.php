<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class WxModel extends CommonModel {




    /**
     * 插入WX登录的信息，
     * $access_token
     * $refresh_token
     * $wxopenid
     * $name
     * $avatar
     * @param unknown $access_token
     * @param unknown $refresh_token
     * @param unknown $qqopenid
     * @param unknown $name
     * @param unknown $avatar
     * @return boolean|unknown
     */
    public function addWxUser($wxopenid,$user_info){
        $avatarpath = $user_info['headimgurl'];
        
        if ($avatarpath == '' || $avatarpath == null || empty($avatarpath)){
            if ($user_info['sex']=='1'){
                $avatarpath = C('default_avatar')[1];
            }else{
                $avatarpath = C('default_avatar')[2];
            }
        }
        
//         $wx_avatar_url = $user_info['headimgurl'];
        
//         $avatarpath=$this->wxCutImage($wx_avatar_url);
            
//         $wx_avatarpath='.'.$avatarpath;
//         //裁剪wx的头像
//         $cut_wxavatar=$this->imagecut($wx_avatarpath);
            
//         if ($cut_wxavatar !== false){
//             $avatarpath=$cut_wxavatar;
//         }
        
        if ($user_info['sex']=='1'){
            $sex=1;
        }else {
            $sex=2;
        }
        $User=M('User');
        $userdata['avatar']=$avatarpath;
        $userdata['lastname']=$user_info['nickname'];
        $userdata['sex']=$sex;
        $userdata['ctime']=current_datetime();
        $userId=$User->data($userdata)->filter('strip_tags')->add();//微信登录第一次时为此微信用户创建本站的用户账号信息
        if (!$userId){
            return $User->getDbError();
        }
        $create['access_token']=$_SESSION['access_token'];
        $create['expires_in']=$_SESSION['expires_in'];
        $create['refresh_token']=$_SESSION['refresh_token'];
        $create['openid']=$wxopenid;
        $create['name']=$user_info['nickname'];
        $rules = array(
        );
        $auto = array (
        );
        $addwx=$this->validate($rules)->auto($auto)->create($create);//创建第一次微信登录的信息对象
        if (!$addwx){
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
        $data['status']=1;
        $wxid=$this->data($data)->filter('strip_tags')->add();//保存第一次微信登录获取的信息

        if (!$wxid){
            //             return false;
            return $this->getDbError();
        }

        // 登陆成功，设置thirduser的二维数组
        session('user.id',$userId);  						        //设置session
        session('user.name',$user_info['nickname']);  	            //设置session
        session('user.avatar',$avatarpath);  					    //设置session

        session('shopkeeper',null);
        session('shop_auto_login',null);


        // 		return $userId;
        return true;
    }


    /**
     * 插入WX登录的信息，
     * $access_token
     * $refresh_token
     * $wxopenid
     * $name
     * $avatar
     * @param unknown $access_token
     * @param unknown $refresh_token
     * @param unknown $qqopenid
     * @param unknown $name
     * @param unknown $avatar
     * @return boolean|unknown
     */
    public function addWxShopkeeper($wxopenid,$user_info,$sex){

        //header("content-Type: text/html; charset=Utf-8");//设置字符编码
// print_r($user_info['headimgurl']);exit;

        $avatarpath=GrabImage($user_info['headimgurl']);

        $wx_avatarpath='.'.$avatarpath;
        $cut_wxavatar=$this->shopimagecut($wx_avatarpath);//裁剪wx的头像
        if ($cut_wxavatar===false){
            return $cut_wxavatar;
        }
        $avatarpath=$cut_wxavatar;

        // TODO  不知道商家根据微信号注册这个功能写不写好

        $shopkeeperModel=D('Common/Shopkeeper');
        $userdata['avatar']=$avatarpath;
        $userdata['lastname']=$user_info['nickname'];
        $userdata['sex']=$sex;
        $userdata['ctime']=current_datetime();
        $userId=D('User')->data($userdata)->filter('strip_tags')->add();//QQ登录第一次时为此QQ用户创建本站的用户账号信息
        if (!$userId){
            //             return false;
            return D('User')->getDbError();
        }

        $create['access_token']=$_SESSION['access_token'];
        $create['expires_in']=$_SESSION['expires_in'];
        $create['refresh_token']=$_SESSION['refresh_token'];
        $create['openid']=$wxopenid;
        $create['name']=$user_info['nickname'];
        $rules = array(
        );
        $auto = array (
        );
        $addwx=$this->validate($rules)->auto($auto)->create($create);//创建第一次QQ登录的信息对象
        if (!$addwx){
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
        $data['status']=1;
        $wxid=$this->data($data)->filter('strip_tags')->add();//保存第一次QQ登录获取的信息

        if (!$addwx){
            //             return false;
            return $this->getDbError();
        }

        // 登陆成功，设置thirduser的二维数组
        session('user.id',$userId);  						        //设置session
        session('user.name',$user_info['nickname']);  	            //设置session
        session('user.avatar',$avatarpath);  					    //设置session

        session('shopkeeper',null);
        session('shop_auto_login',null);

        // 		return $userId;
        return true;
    }

    /**
     * 检查某个openid是否登录过，登录过就返回该wx的相关信息，没登录过的话就返回flase
     * @param string $openid
     * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function checkopenid($openid=''){
        $rel=$this->where("openid='%s'",$openid)->find();
        if (!$rel){
            return false;
        }
        session('wx.openid',$openid);
        return $rel;
    }

    /**
     * 根据uid，判断该用户uid是否有过微信登录--没有返回false--暂时没有用
     * @param number $uid
     * @return boolean|Ambigous <\Think\mixed, NULL, mixed, multitype:Ambigous <unknown, string> unknown , unknown, object>
     */
    public function checkUid($uid=0){
        if (!$uid){
            return false;
        }
        $rel = $this->where('uid=%d',$uid)->getField('openid');
        if (!$rel){
            return false;
        }
        return $rel;
    }
    /**
     * 根据uid数组，获取该集合的用户信息---没有返回false
     * @param unknown $uid
     * @return boolean|Ambigous <\Think\mixed, boolean, string, NULL, mixed, multitype:, unknown, object>
     */
    public function checkUidlist($uid=array()){
        if (empty($uid)){
            return false;
        }
        $uidImplode = implode(',', $uid);
        $map['uid'] = array('IN',$uidImplode);
        $rel        = $this->where($map)->select();
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
    public function shopimagecut($path=''){
        $upload_path = './Public/Uploads/';
        $db_path = 'shop_avatar/' . uniqid() . '.jpg';
        $thumb = $upload_path . $db_path;
        $thumbimg = dirname($thumb);
        $this->mk_dir($thumbimg);

        $image = new \Think\Image();
        $image->open($path);
        // 生成一个固定大小为75*75的缩略图并保存为thumb.jpg
        $imageInfo=$image->thumb(75, 75,\Think\Image::IMAGE_THUMB_FIXED)->save($thumb);
        if (!$imageInfo){
            return false;
        }
        $unimagestatus=unlink($path);		//删除//

        return $db_path;
    }

    

    /**
     * 关联已有用户的账号, 
     * @param unknown $uid
     */
    public function relationToUid($userinfo=array()){
        $openid = session('wx.openid');
        if (!$userinfo['id'] || !$openid){
            return false;
        }
        $check_exist = $this->checkopenid($openid);
        if ($check_exist!==false){
            return false;
        }
        $data   = array();
        $data['uid']    = $userinfo['id'];
        $data['openid'] = $openid;
        $data['name']   = $userinfo['firstname'].$userinfo['lastname'];
        $data['update_time']  = time();
        $rel    = $this->add($data);
        if (!$rel){
            return false;
        }
        // 登陆成功，设置user的二维数组
        session('user.id',$userinfo['id']);  						//设置session
        session('user.name',$userinfo['firstname'].$userinfo['lastname']);  	//设置session
        session('user.remark',$userinfo['remark']);  						//设置session
        session('user.profession',$userinfo['profession']);  						//设置session
        session('user.phone',$userinfo['phone']);  						//设置session
        session('user.email',$userinfo['email']);  						//设置session
        session('user.avatar',$userinfo['avatar']);  					//设置session
        session('user.telstatus',$userinfo['telstatus']);  					//设置session
        session('user.vtype',$userinfo['vtype']);  					//设置session
        session('user.vstatus',$userinfo['vstatus']);  					//设置session
        session('shopkeeper',null);
        session('shop_auto_login',null);
        return true;
    }

    /**
     * 微信发送---跟约信息---模板消息
     * @param unknown $userList
     * @param unknown $groupTitle
     * @param unknown $gid
     * @param unknown $assistNUm
     * @param unknown $pushedNUm
     * @return boolean
     */
    public function sendAssist($userList , $groupTitle , $gid , $assistNUm , $pushedNUm){
        $wxList = $this->checkUidlist($userList);
        if (!empty($wxList)){
            require_once('./Api/wx/config.php');
            require_once('./Api/wx/wx_def.php');
            $wxWeChat = new \wechatCallbackapiTest( WX_APP_ID , WX_APP_SECRET );
            $content  = ':你发布的“'.$groupTitle.'”有用户跟约啦啦';
            $url      = 'http://17yueke.cn/g/'.$gid;
            $title    = '用户跟约通知';
            $datetime = date('m月d日');
            $messageone = '该心愿单跟约人数';
            $messagetwo = '该心愿单商家推送课程数';
            foreach ($wxList as $wxkey=>$wxvalue){
                $result[] = $wxWeChat->sendTemplateMessage($wxvalue['openid'], $url, $title, $datetime, $wxvalue['name'], $content, $messageone, $assistNUm, $messagetwo, $pushedNUm);
            }
            
            foreach ($result as $wkey=>$wvalue){
                if ($wvalue['errcode']!=0){
                    
                }
            }

            return true;
        }

        return false;
        
    }
    
    /**
     * 客服主动发送消息---跟约
     * @param unknown $userList
     * @param unknown $groupTitle
     * @param unknown $gid
     * @param unknown $assistNUm
     * @param unknown $pushedNUm
     * @return mixed
     */
    public function kfMessage($userList , $groupTitle , $gid , $assistNUm , $pushedNUm , $groupUrl){
        $wxList = $this->checkUidlist($userList);
        if (!empty($wxList)){
            require_once('./Api/wx/config.php');
            require_once('./Api/wx/wx_def.php');
            $wxWeChat = new \wechatCallbackapiTest(WX_APP_ID,WX_APP_SECRET);
            $openidArray  = array();
            $contentArray = array();
            foreach ($wxList as $wxkey=>$wxvalue){
                $openidArray[] = $wxvalue['openid'];
                $contentArray  = '用户通知
'.date('m月d日').'
'.$wxvalue["name"].'您好~你发布的【'.$groupTitle.'】有用户跟约啦,快快点击查看,是谁跟约了你的心愿单吧'.$groupUrl.'
                            
进入详情点击右上角分享,可将职位分享到朋友圈或者发送给朋友。
                            
该心愿单跟约人数：'.$assistNUm.' 
该心愿单商家推送课程数：'.$pushedNUm;
            }
            $kfMessageStaus = $wxWeChat->initiativeReply($openidArray, $contentArray, 'text');
            return $kfMessageStaus;
        }
        
    }
    
//---------------------------------------------Wx微信后台-----------------------------------------------------------------------------    
    /**
     * 微信用户注册的数据调出
     * @param unknown $pageOffset
     * @param number $perPage
     * @param string $order
     * @return unknown
     */
    public function wxList($pageOffset,$perPage=10,$order='desc'){
        $alias = 'wx';//定义当前数据表的别名
        //要查询的字段
        $array = array(
            'wx.openid',
            'wx.uid',
            'wx.relation',
            'u.id',
            'u.firstname',
            'u.lastname',
            'u.phone',
            'u.email',
        );
        $join  = array(
            '__USER__ u on u.id = wx.uid',
        );
        $where['relation'] = array('EQ',0);
        
        $order = 'wx.id '.$order;
        $res=$this  -> alias($alias)
                    -> join($join)
                    -> where($where)
                    -> order($order)
                    -> limit($pageOffset,$perPage)
                    -> field($array)
                    -> select();
        return $res;
    }

    /**
     * 对该一个wx openid出现的次数进行分页
     * @param number $curPage
     * @param number $perPage
     * @return Ambigous <\Common\Util\multitype:number, multitype:number >
     */
    public function pageAll($curPage=1,$perPage=10){
        import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
        $counts = $this->count(); // 查询满足要求的总记录数
        $Page  = new \Common\Util\AjaxPage($counts,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
        $pageArray = $Page->getCounts();
        return  $pageArray;
    }
    
    
    
    
    public function getWxAvatar(){
        $alias = 'wx';
        $array = array(
            'wx.id',
            'wx.uid',
            'u.avatar',
        );
        $join = array(
            '__USER__ u on u.id=wx.uid',
        );
        
    }
    
    /**
     * 微信头像转移
     * 微信服务器到17服务器
     * 抓取头像
     * 裁剪头像
     * @param string $url
     * @return string|boolean
     */
    public function updataWxShift($url=''){
        $imgPath = $this->wxCutImage($url);
        if (!file_exists($imgPath)){
            return '图像抓取失败!';
        }
        $imgPath = '.'.$imgPath;
        $thumbAvatar = $this->imagecut();
        if (!$thumbAvatar){
            return '图像裁剪处理失败！';
        }
        session('wx.thumb',$thumbAvatar);
        return true;
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
        $thumb='.'.$user_avatar.'/wx/'.$pathname.'/'.$imgname.'.jpg';
    
        $thumbimg='.'.$user_avatar.'/wx/'.$pathname;
        $this->mk_dir($thumbimg);
    
        $image = new \Think\Image();
        $image->open($path);
        // 生成一个固定大小为75*75的缩略图并保存为thumb.jpg
        $imageInfo=$image->thumb(75, 75,\Think\Image::IMAGE_THUMB_FIXED)->save($thumb);
        if (!$imageInfo){
            return false;
        }
        $unimagestatus=unlink($path);		//删除// 
    
        $thumbdeldian=$user_avatar.'/wx/'.$pathname.'/'.$imgname.'.jpg';
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
    
    //
    // 变量说明:
    // $url 是远程图片的完整URL地址，不能为空。
    // $filename 是可选变量: 如果为空，本地文件名将基于时间和日期
    // 自动生成.
    public function wxCutImage($url,$filename="") {
        if($url==""){
        return false;
        }
            if($filename=="") {
                $filename=md5(date("dMYHis")).'.jpg';
        }
        $qqpath=C('user_avatar');
        $filepath=$qqpath.'/wx/'.$filename;
        $filename='.'.$qqpath.'/wx/'.$filename;

        file_put_contents($filename, file_get_contents($url));
        return $filepath;
    }
    
    
    
    
    
}
