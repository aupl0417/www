<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class GroupAssistModel extends CommonModel {
	
    //验证
    protected $_validate=array(
        array('whoid', '/^\d+$/', '用户ID不是数字！', 1, 'regex'),
        array('groupid', '/^\d+$/', '约课ID不是数字！', 1, 'regex'),
    );
    

    protected $_auto = array (
        array('ctime', 'current_datetime', 1, 'function')
    );
    
    /**
     * 添加一条跟约信息
     * @param number $uid
     * @param number $gid
     * @return string|boolean
     */
    public function addAssistByUser($uid=0,$gid=0){
        
        $overtimestatus=D('GroupInfo')->getOvertimeByGid($gid);
        if ($overtimestatus===0){
            return '该约课已经截止';
        }
        
        
        //评分
        $user_num=D('User')->userScore();
        if ($user_num<40){
            return '请先完善资料';
        }
        $user_avatar_true=D('User')->userAvatars($uid);
        if ($user_avatar_true!==true){
            return '请先上传头像';
        }
        
        
        
        //一天之内，该用户对该约课只能 跟约2次，取消2次
        $assist_name='assistnum'.$uid.$gid;
        $assist_num = S($assist_name);
        $date = getdate();
        $endTime = mktime(23, 59, 59, $date['mon'], $date['mday'], $date['year']);
        $timesy=$endTime-time();
        if (!$assist_num){
            S($assist_name,1,$timesy);
        }elseif (intval($assist_num)<4){
            $num=intval($assist_num)+1;
            S($assist_name,$num,$timesy);
        }else {
            return '一天只能对该约课跟约2次，请勿重复跟约!';
        }
        
        $_POST['whoid']=$uid;
        $_POST['groupid']=$gid;
        if (!$this->create()){
            return $this->getDbError();
        } 
        $checkNow=$this->checkUserBygid($uid,$gid);//有木有跟约过该课程
        if ($checkNow){
        	$delnum=$this->delAssiByGid($gid,$uid);//存在跟约，删除跟约信息
        	if ($delnum!==true) {
        		return $delnum;
        	}
        	return '404';//已经跟约成功并且删除成功
//             return '402';//已经跟约
        }
        $rel = $this->add();
        if(!$rel){
            return $this->getDbError();
        }
        $addone=D('GroupInfo')->addAssistOne($gid);
        $sendAllSnsAssist=$this->sendSnsAllAssist($gid,$uid);//所有跟约用户发通知短信
        return true;
    }
	
    
//----------------------------游客跟约处理
    public function addAssistByVisitor($gid=0,$visitorId=0){

        $overtimestatus=D('GroupInfo')->getOvertimeByGid($gid);
        if ($overtimestatus===0){
            return '该约课已经截止';
        }

        //一天之内，该用户对该约课只能 跟约2次，取消2次
        $assist_name='assistnum'.$visitorId.$gid;
        $assist_num = S($assist_name);
        $date = getdate();
        $endTime = mktime(23, 59, 59, $date['mon'], $date['mday'], $date['year']);
        $timesy=$endTime-time();
        if (!$assist_num){
            S($assist_name,1,$timesy);
        }elseif (intval($assist_num)<4){
            $num=intval($assist_num)+1;
            S($assist_name,$num,$timesy);
        }else {
            return '一天只能对该约课跟约2次，请勿重复跟约!';
        }
        

        $_POST['whoid']=0;
        $_POST['groupid']=$gid;
        $_POST['visitor_id']=$visitorId;
        if (!$this->create()){
            return $this->getDbError();
        }
        $checkNow=$this->checkUserBygid(0,$gid,$visitorId);//有木有跟约过该课程
        if ($checkNow){
            if ($checkNow===4000){
                return '你已跟约!';
            }
            $delnum=$this->delAssiByGid($gid,0,$visitorId);//存在跟约，删除跟约信息
            if ($delnum!==true) {
                return $delnum;
            }
            return '404';//已经跟约成功并且删除成功
        }
        $rel = $this->add();
        if(!$rel){
            return $this->getDbError();
        }
        $addone=D('GroupInfo')->addAssistOne($gid);
        return true;
    }
    
    /**
     * 更新两个字段值，用户的id跟游客的id关联
     * @param number $uid
     * @param number $visitorid
     * @return boolean|Ambigous <boolean, unknown>
     */
    public function updataVisitorToUid($uid=0,$visitorid=0){
        if ($uid==0||$visitorid==0){
            return false;
        }
        $visitAssistGid=$this->field('groupid')->where('visitor_id=%d',$visitorid)->select();
        if (!$visitAssistGid){
            return false;
        }
        
        $userAssistGid=$this->field('groupid')->where('whoid=%d',$uid)->select();
        if ($userAssistGid){
            $userGidList=array_column($userAssistGid,'groupid');
            $visitGidList=array_column($visitAssistGid,'groupid');
            $assistuinter=array_diff($visitGidList,$userGidList);
            $delList=array_intersect($visitGidList,$userGidList);
            $assistPode=implode(',',$assistuinter);
            $delListPode=implode(',',$delList);
        }else {
            $visitGidList=array_column($visitAssistGid,'groupid');
            $assistPode=implode(',',$visitGidList);
        }

// print_r($delListPode);
// print_r("<br/>");
// print_r($assistPode);
// print_r("<br/>");
// print_r($assistPode);
// exit;
        if (!empty($delListPode)){
            $map['groupid'] = array('IN',$delListPode);
            $map['whoid'] = array('EQ',0);
            $map['visitor_id'] = array('EQ',$visitorid);
            $delVisit=$this->where($map)->delete();
        }
        
        if (!empty($assistPode)){
            $data=array(
                'whoid'     =>$uid,
                'visitor_id'=>0,
                'visitor_check_id'=>$visitorid,
            );
            $where['groupid'] = array('IN',$assistPode);
            $map['whoid'] = array('EQ',0);
            $where['visitor_id'] = array('EQ',$visitorid);
            $saveVisit=$this->where($where)->setField($data);
        }

// print_r($delListPode);
// print_r("<br/>");
// print_r($assistPode);
// print_r("<br/>");
// print_r($delVisit);
// print_r("<br/>");
// print_r($saveVisit);
// exit;
        
       if (!$delVisit||!$saveVisit){
            return $delVisit.'|'.$saveVisit;
       }
       return true;
    }
//----------------------------游客跟约处理    ------------------------------------------

    
    /**
     * 根据gid，删除此gid下的跟约
     * @param number $gid,$uid
     * @return Ambigous <\Think\mixed, boolean, unknown>
     */
    public function delAssiByGid($gid=0,$uid=0,$visitorId=0){
        if ($uid==0&&$visitorId==0){//删除该约课的所有跟约
            $delassist = $this->where('groupid=%d',$gid)->delete();
        }elseif ($uid!=0){//删除该人的跟约
            $delassist = $this->where('groupid=%d and whoid=%d',$gid,$uid)->delete();
            D('GroupInfo')->delAssistOne($gid);
        }elseif ($visitorId!=0){
            $delassist = $this->where('groupid=%d and visitor_id=%d',$gid,$visitorId)->delete();
            D('GroupInfo')->delAssistOne($gid);
        }
        if ($delassist===false){
            return $this->getDbError();
        }
        return true;
    }
    
    
    
    /**
     * 检查 该用户 是否已经 跟约 该约课
     * @param number $uid
     * @param number $gid
     * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function checkUserBygid($uid=0,$gid=0,$visitorId=0){
        if ($visitorId==0){//该用户是否跟约过
            $check = $this->where('whoid=%d and groupid=%d',$uid,$gid)->find();
            return $check;
        }else {//该游客是否跟约过
            $check = $this->where('visitor_id=%d and groupid=%d',$visitorId,$gid)->find();
            if (!$check){
                $check_visit=$this->where('visitor_check_id=%d and groupid=%d',$visitorId,$gid)->find();
                if (!$check_visit){
                    return false;
                }
                return 4000;
            }
            return $check;
        }
    }
    
    
    
    /**
     * 获取该条约课信息的 总应约数===约课详情也用到了
     * @param number $gid
     */
    public function groupcount($gid=0){
        $num    = $this->where('groupid=%d',$gid)->count();
        return $num;
    }
	
	

    /**
     * 获取某用户的约课的ID，并且分页，AND  获取约课ID的详细信息
     * @param number $curPage
     * @param number $perPage
     * @return string
     */
    public function userAssist($uid,$curPage=1,$perPage=2){
           $pageArray = $this->assistPage($curPage,$perPage,$uid); // 查询满足要求的总记录数
           $rel    = $this->field('groupid')->where('whoid=%d',$uid)->limit($pageArray['pageOffset'],$pageArray['perPage'])->select();
           if (!$rel){
                   return $rel;
           }
           $groupinfo = D('GroupInfo');
           foreach ($rel  as $key=>$value){
                  $rel[$key]=$groupinfo->userGroupInfo(0,$value['groupid'],0,1);
           }
    	   $data =  array(
    	       'info' => $rel,
    	       'page' => $pageArray,
    	   );
           return $data;
    }
	
	
    /**
     * 根据$uid分页
    *约课组课分页数据
    * 返回约课组团分页信息
    * @param int $curPage
    * @param int $perPage
    * @return array $page
    */
    public function assistPage($curPage=1,$perPage=5,$uid=0){
        import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
        $count= $this->assistCount($uid); // 查询满足要求的总记录数
        $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
        $pageArray=$Page->getCounts();
        return $pageArray;
     }

	
//=================================================================	
    /**
    * 返回某用户的所约课的总组课数
    * @param number $uid
    * @return unknown
    */
    public function assistCount($uid=0){
        $count = $this->where('whoid=%d',$uid)->count();
        return $count;
    }
	
	
	

    //消息约课组团
    public function assistInfo($uid=0,$curPage=1, $perPage=2,$order='DESC'){
        $groupid = D('GroupInfo');
        $group = $groupid->groupidByUser($uid);//$group获取该用户发布过的课程信息id集合
        if (!$group){
            return $group;
        }
        foreach ($group as $kg=>$vg){
            $gidArray[$kg] = $vg['id'];
        }
        $gidnum = implode(",", $gidArray);
        $page = $this->pageByGid($curPage, $perPage, $gidnum);
        $alias = 'ga';//定义当前数据表的别名
        //要查询的字段
        $array = array(
            'ga.id as commentid',
            'ga.whoid as uid',
            'ga.groupid',
            'ga.ctime as gactime',
            'gi.ctime',
            'gi.title',
            'u.avatar as zkavatar',
            'u.firstname',
            'u.lastname',
            'skd.avatar skavatar',
            'skd.environ',
        );
        $join  = array(
            '__GROUP_INFO__ gi on gi.id = ga.groupid',
            '__USER__ u on u.id = ga.whoid',
            'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gi.sid'
        );
        $map['ga.groupid']  = array('in',$gidnum);
        $order='ga.ctime '.$order;
        $res=$this  -> alias($alias)
                    -> join($join)
                    -> where($map)
                    -> order($order)
                    -> limit($page['pageOffset'],$page['perPage'])
                    -> field($array)
                    -> select();
        
        $userNews=D('UserNews');
        $addnews=$userNews->addCheckTime($uid,1);//更新读取消息的时间
        
        if (!$res){
            return $res;
        }

        
        foreach ($res as $key=>$value){
            $res[$key]['skavatar'] = '/Public/Uploads/'.$value['skavatar'];//商家的头像
            $res[$key]['environ'] = '/Public/Uploads/'.$value['environ'];//
            $res[$key]['gactime'] = transDate($value['gactime']);
            $res[$key]['name'] = $value['firstname'].$value['lastname'];
        }
        
        
        
        $data = array(
            'info'  =>  $res,
            'page'  =>  $page,
        );
        return $data;
    }

    /**
     * 根据传入的gid一位 集合 获取有组团过in在此数组范围内的  评论数
     * @param array $gid
     * @return unknown
     */
    public function countByUser($gid){
        $map['groupid']  = array('in',$gid);
        $counts = $this->where($map)->count();
        return $counts;
    }

    /**
     * gid集合出现的总数进行分页
     * 对该用户的 约课评论数 进行 约课分页
     * @param number $curPage
     * @param number $perPage
     * @param string $gid集合
     * @return Ambigous <\Common\Util\multitype:number, multitype:number >
     */
    public function pageByGid($curPage=1,$perPage=2,$gid=''){
	   import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	   $count= $this->countByUser($gid); // 查询满足要求的总记录数
	   $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	   $pageArray=$Page->getCounts();
	   return  $pageArray;
    }
    
    
    /**
     * 返回该uid的还没读的数据记录数
     * @param unknown $uid
     */
    public function newsNumAssist($uid){
        $userNews=D('UserNews');
        $newsOldTime=$userNews->checktime($uid,1);
        $newsNum=$this->dayutime($uid,$newsOldTime['checktime']);
        return $newsNum;
    }
    
    
    
    /**
     * 返回新的插入的数据的记录数，根据uid
     * @param string $oldtime
     * @return unknown
     */
    public function dayutime($uid=0,$oldtime=''){
        $groupid = D('GroupInfo');
        $group = $groupid->groupidByUser($uid);//$group获取该用户发布过的课程信息id集合
        if (!$group) {
        	return 0;
        }
        foreach ($group as $kg=>$vg){
            $gidArray[$kg] = $vg['id'];
        }
        $gidnum = implode(",", $gidArray);
        $map['ctime']  = array('GT',$oldtime);
        $map['groupid']= array('IN',$gidnum);
        $nums=$this->where($map)->count();
        return $nums;
    }
    
//==============================================
    
    /**
     * 根据gid 获取该约课的组团的人的信息
     * @param number $gid
     * @param number $curPage
     * @param number $perPage
     * @param string $order
     * @return unknown
     */
    public function assistByGid($gid=0,$curPage=1,$perPage=2,$order='desc'){
        $page = $this->pageByThisGid($gid,$curPage,$perPage);
        $alias = 'ga';//定义当前数据表的别名
        //要查询的字段
        $array = array(
            'ga.whoid as uid',
            'ga.visitor_id',
            'u.firstname',
            'u.lastname',
            'u.phone',
            'u.email',
            'u.avatar',
            'vi.name',
        );
        $join  = array(
            'left join __USER__ u on u.id = ga.whoid',
            'left join __VISITOR__ vi on vi.id = ga.visitor_id',
        );
        $where='ga.groupid='.$gid;
        $order='ga.ctime '.$order;
        $res=$this  -> alias($alias)
                    -> join($join)
                    -> where($where)
                    -> order($order)
                    -> limit($page['pageOffset'],$page['perPage'])
                    -> field($array)
                    -> select();
        if (!$res){
            return $res;
        }
        $visitor_avatar=C('visitor_config')['avatar'];
        foreach ($res as $key=>$value){
            if ($value['uid']==0){
                $res[$key]['lastname'] = $value['name'];
                $res[$key]['avatar'] = $visitor_avatar;
            }
        }
        $rel['assist'] = $res;
        $rel['pageAll'] = $page['pageAll'];
        $rel['page'] = $page;
        return $rel;
    }
    
    
    /**
     * 根据gid统计数据,统计该一个gid出现的次数
     * @param number $gid
     * @return unknown
     */
    public function countByGid($gid=0){
        $counts = $this->where('groupid=%d',$gid)->count();
        return $counts;
    }
    
    /**
     * 对该一个gid出现的次数进行分页
     * @param number $gid
     * @param number $curPage
     * @param number $perPage
     * @return Ambigous <\Common\Util\multitype:number, multitype:number >
     */
    public function pageByThisGid($gid=0,$curPage=1,$perPage=2){
	   import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
	   $count= $this->countByGid($gid); // 查询满足要求的总记录数
	   $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
	   $pageArray=$Page->getCounts();
	   return  $pageArray;
    }
    
    
    
    
    
//==========================================================================================
    /**
     * 根据 被推送的课程 发送短信给，该约课的  跟约的全部用户
     * @param number $gid
     * @return unknown
     */
    public function sendSnsPushed($gid=0){
        $alias='ga';
        $arrayAssist = array(
            'ga.id',
            'ga.whoid',
            'ga.groupid',
	        'u.firstname',
	        'u.lastname',
            'u.phone',
            'u.email',
            'u.id as uid',
        );
        
        $joinAssist  = array(
            'left join __USER__ u on u.id = ga.whoid',
        );
        
        $GroupAssist=$this  -> alias($alias)
                            -> join($joinAssist)
                            -> where('groupid=%d',$gid)
                            -> field($arrayAssist)
                            -> select();
        return $GroupAssist;
    }
//==================================================================================================

    /**
     * 该约课下的  所有跟约用户都发送一条跟约信息
     * @param number $gid
     */
    public function sendSnsAllAssist($gid=0,$uid=0){
        //该约课跟约人数
        $assistNUm = $this->countByGid($gid);
        //该约课商家推送课程数
        $pushedNUm = D('GroupPushed')->countByGid($gid);
        if (!$assistNUm){
            $assistNUm = 0;
        }
        if (!$pushedNUm){
            $pushedNUm = 0;
        }
        $groupTitle = M('GroupInfo')->where('id=%d',$gid)->getField('title');
        
        $GroupInfo = D('GroupInfo');
        $issueUser = $GroupInfo->whoIssueByGid($gid);//获取该约课发布人的联系方式 
        $allUserAssist = $this->sendSnsPushed($gid);//获取该约课的所有跟约人 
        array_push($allUserAssist, $issueUser);

//         //去除相同的uid值
//         $array_assist=array();
//         $i_assist_un=0;
//         foreach ($allUserAssist as $keysns=>$valuesns){
//             if ($allUserAssist['uid']!=$valuesns['uid']){
//                 $array_assist[$keysns] = $valuesns;
//             }else {
//                 $i_assist_un++;
//                 if ($i_assist_un===1){
//                     $array_assist[$keysns] = $valuesns;
//                 }
//             }
//         }


        
        //去除相同的uid值
        $array_assist=array();
        $i_assist_un=0;
        foreach ($allUserAssist as $keysns=>$valuesns){
            if ($issueUser['uid']!=$valuesns['uid']){
                $array_assist[$keysns] = $valuesns;
            }else {
                $i_assist_un++;
                if ($i_assist_un===1){
                    $array_assist[$keysns] = $valuesns;
                }
            }
        }


        
        //去除，当时发布人跟约的时候，不发送信息给该约课的发布人
        $array_assist_all=array();
        foreach ($array_assist as $keyAllNum=>$valueAllNum){
            if ($valueAllNum['uid']!=$uid){
                $array_assist_all[$keyAllNum] = $valueAllNum;
            }
        }


        $emails=array();
        $phones=array();
        $userList=array();
        foreach ($array_assist_all as $key=>$row) {
            $userList[] = $row['uid'];
            if ($row['phone']) { // 这个user使用手机作为联系方式
                $phones[$key]['phone'] = $row['phone'];
            }else if ($row['email']) { // 这个user是使用邮箱作为联系方式
                $emails[$key]['email'] = $row['email'];
            } 
        }      
        $phonecode=array();
        $emailcode=array();
        $subject='【17约课】';
        $groupUrl   = '17yueke.cn/g/'.$gid;
        $body='亲～您在17约课上跟约的约课单，已经有人约你了哟，赶紧戳'.$groupUrl.' 看看约你的是帅哥还是美女吧～么么哒';
        if (!empty($emails)){
            foreach ($emails as $emailkey=>$emailvalue){
                $emailcode[]=sendMail($emailvalue['email'],$body, $subject);
            }
        }
        if (!empty($phones)){
            require_once('./Api/sms/sms_send.php');
            foreach ($phones as $telkey=>$telvalue){
                $msg=$body.$subject;//【17约课】 可以换成自己的签名，签名一般用公司或网站的简称
                $phonecode[]=sendnote($telvalue['phone'],urlencode(mb_convert_encoding($msg, 'gbk' ,'utf-8')));//如果你网站或软件的格式是utf-8的，需要对发送的内容转成gbk的
            }
        }
        
        
        //------微信------
        
        $wx = D('Wx');
        $wxSendStaus = $wx->kfMessage($userList , $groupTitle , $gid , $assistNUm , $pushedNUm , $groupUrl);
//         $wx->sendAssist($userList , $groupTitle , $gid , $assistNUm , $pushedNUm);
        
//         $wxList = $wx->checkUidlist($userList);
//         if (!empty($wxList)){
//             require_once('./Api/wx/wx_def.php');
//             $wxWeChat = new \wechatCallbackapiTest(APP_ID,APP_SECRET);
//             $content  = ':你发布的“'.$groupTitle.'”有用户跟约啦啦';
//             $url      = 'http://17yueke.cn/g/'.$gid;
//             $title    = '用户跟约通知';
//             $datetime = date('m月d日');
//             $messageone = '该心愿单跟约人数';
//             $messagetwo = '该心愿单商家推送课程数';
//             foreach ($wxList as $wxkey=>$wxvalue){
//                 $result[] = $wxWeChat->sendTemplateMessage($wxvalue['openid'], $url, $title, $datetime, $wxvalue['name'], $content, $messageone, $assistNUm, $messagetwo, $pushedNUm);
//             }
            
//         }
//         print_r($result);exit;
        
        $dataarray['gtitle']=$groupTitle;
        $dataarray['wx']    =$wxSendStaus;
        
        $dataarray['status']=200;
        $dataarray['emails']=$emails;
        $dataarray['phones']=$phones;
        $dataarray['emailcode']=$emailcode;
        $dataarray['phonecode']=$phonecode;
        return $dataarray;
    }
    
    
    
    
    
    
    
	
}