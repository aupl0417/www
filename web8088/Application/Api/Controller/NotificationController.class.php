<?php

namespace Api\Controller;
use Api\Controller\CommonController;

/**
 * @author JM_Joy
 * @author users
 */
class NotificationController extends CommonController {

    public function index() {
        echo "hello world";
    }


    public function numUserEnrollShop($p = '', $t = '') {
        // 检验密码
        if ($p != '12346789') {
            return $this->ajaxReturn([
                'status'    =>  403,
            ]);
        }

        // 哪个时间点
        switch ($t) {
        case '10':
            $beginTime = strtotime('-18 hours');
            break;

        case '16':
            $beginTime = strtotime('-6 hours');
            break;

        default:
            $this->ajaxReturn([
                'status'    =>  404,
            ]);
        }

        // 查询这个时间段的被报名商家的联系方式
        M('ShopInfoUser')->field([
            //'siu.shop_info_id',
            'count(siu.id) `user_count`',
            's.id',
            's.login_email',
            's.login_phone',
        ]);

        $resArr = M('ShopInfoUser')->alias('siu')
                                    ->join('__SHOP_INFO__ si on si.id = siu.shop_info_id')
                                    ->join('__SHOPKEEPER__ s on s.id = si.sid')
                                    ->group('s.id')
                                    ->where('siu.ctime > %d and siu.ctime <= %d', $beginTime, time())
                                    ->select();

        // 分成两批
        $emails = [];
        $phones = [];

        $msg = '您好！您在17约课上发布的课程，已经有%d位用户报名您的课程，请及时查看。登录17yueke.cn。【17约课】';

        foreach ($resArr as $row) {
            if ($row['login_email']) { // 这个商家是使用邮箱作为联系方式
                $emails[] = [
                    'email'     =>  $row['login_email'],
                    'msg'       =>  sprintf($msg, $row['user_count']),
                ];

            } else if ($row['login_phone']) { // 这个商家使用手机作为联系方式
                $phones[] = [
                    'phone'     =>  $row['login_phone'],
                    'msg'       =>  sprintf($msg, $row['user_count']),
                ];

            }
        }

        // 发送邮件或者短信
        foreach ($emails as $row) {
            sendMail($row['email'], $row['msg'], '17约课：已经有用户报名您的课程了');
        }

        require_once(realpath('Api/sms/sms_send.php'));
        foreach ($phones as $row) {
            sendnote($row['phone'], urlencode(iconv('utf-8', 'gbk', $row['msg'])));
        }

        $this->ajaxReturn([
            'status'    =>  200,
            'emails'    =>  $emails,
            'phones'    =>  $phones,
        ]);
    }

// ====================================================================================================

    public function userPushedForShop($p=0,$t=0){
        // 检验密码
        if ($p != '12346789') {
            return $this->ajaxReturn([
                'status'    =>  403,
            ]);
        }
// header("Content-type:text/html;charset=utf-8");
        $pushedByUser = D('GroupPushed');
        $result       = $pushedByUser->userForShop($t);
        if ($result['status']!=200){
            $this->ajaxReturn(array(
                'status'    => 400,
                'data'      => $result,
            ));
        }
        $this->ajaxReturn([
            'status'    =>  200,
            'data'      =>  $result,
        ]);
    }


}
