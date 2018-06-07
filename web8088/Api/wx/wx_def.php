<?php
// define("TOKEN", "weixin0827");//改成自己的TOKEN 
// define('APP_ID', 'wx0d32683dca7ad665');//改成自己的APPID 
// define('APP_SECRET', '31fe71a2bd78adaffcf761d1bb82ffe3');//改成自己的APPSECRET 
 

 
class wechatCallbackapiTest 
{ 
    private $fromUsername; 
    private $toUsername; 
    private $times; 
    private $keyword; 
    private $app_id; 
    private $app_secret; 
    
    
    
    public $welcome = '亲，欢迎关注17约课！[鼓掌]找培训，上17约课（www.17yueke.cn）,我们致力于整合优质的教育培训机构以及名师，让同学们拥有选择优质课程的能力。您可以点击下方菜单体验:

1、如何找到好的培训机构呢？[疑问]
第一步：点击【找培训】。
第二步：选择相关培训种类。
第三步：在课程筛选区，挑选心仪的课程吧！

2、如何定制符合自身情况的课程呢？[疑问]
第一步：点击【私人定制】。
第二步：访问官网并发布课程心愿单。

3、“个人中心”有什么用？[疑问]
（1）【绑定账号】后，可以收到跟约您心愿单的用户通知，就这么简单！[拥抱]
（2）领取【奖学金】，我们要让同学们上优质课，得优恵价！[礼物]
（3）使用中"有问题"?直接输入您的问题，17约课客服5分钟内为您服务！[握手]';
    
    public $bindingaccount='绑定账号后，可以收到跟约您心愿单的用户通知，及还有谁跟你一起组团报名课程的通知，就怎么简单。[太阳]';
    public $scholarship='输入“奖学金”关键词，系统会自动向您推送奖学金哟 [礼物]';
    public $feedback='亲！你的意见对我们非常重要，你可以直接回复文字或语音将你对“17约课”平台的期望告诉我们，这些意见将指导我们更好的完善平台，为您提供更优质的服务。服务热线：020-28107517 [拥抱]';
     
    public $allgaokao='感谢你参与「一句话证明你高考过」回复有奖活动.
若你想上化妆课,请回复【1】；
魔术课请回复【2】；
游泳课请回复【3】.
最终获得免费体验课的名额以先发先得为准,名单会公布在微信上.希望你持续关注17约课.谢谢';
    
    public $thxgaokao='感谢你的参与，名单会在周六公布';
    
    public function __construct($appid,$appsecret) 
    { 
        # code... 
        $this->app_id = $appid; 
        $this->app_secret = $appsecret; 
    } 
    /**
     * 验证token
     */
    public function valid() 
    { 
        $echoStr = $_GET["echostr"]; 
        if($this->checkSignature()){ 
            echo $echoStr; 
            exit; 
        } 
    } 
    /** 
     * 运行程序 
     * @param string $value [description] 
     */ 
    public function Run() 
    { 
        $this->responseMsg(); 
        $arr[]=$this->allgaokao;
//         $arr[]= '您好，这是自动回复，我现在不在，有事请留言，我会尽快回复你的[愉快]'; 
        echo $this->make_xml("text",$arr); 
    } 
    /**
     * 被动消息--业务罗辑
     */
    public function responseMsg() 
    {    
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];//返回回复数据 
        if (!empty($postStr)){ 
//                 $access_token = $this->get_access_token();//获取access_token 
                //$this->createmenu($access_token);//创建菜单 
                //$this->delmenu($access_token);//删除菜单 
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA); 
                $this->fromUsername = $postObj->FromUserName;//发送消息方ID 
                $this->toUsername = $postObj->ToUserName;//接收消息方ID 
                $this->keyword = trim($postObj->Content);//用户发送的消息 
                $this->times = time();//发送时间 
                $MsgType = $postObj->MsgType;//消息类型 
                if($MsgType=='event'){ 
                    $MsgEvent = $postObj->Event;//获取事件类型 
                    if ($MsgEvent=='subscribe') {//订阅事件 
                        $arr[] = $this->welcome; 
                        echo $this->make_xml("text",$arr); 
                        exit; 
                    }elseif ($MsgEvent=='CLICK') {//点击事件 
                        $EventKey = $postObj->EventKey;//菜单的自定义的key值，可以根据此值判断用户点击了什么内容，从而推送不同信息 
                        switch ($EventKey){
                            case "bindingaccount":
                                $arr[]=$this->allgaokao;
//                                 $arr[] = $this->bindingaccount;
                                break;
                            case "scholarship":
                                $arr[]=$this->allgaokao;
//                                 $arr[] = $this->toucard();
//                                 $arr[] = $this->scholarship;
                                break;
                            case "feedback":
                                $arr[]=$this->allgaokao;
//                                 $arr[] = $this->feedback;
                                break;
                            default:
                                $arr[] = $EventKey;
                                break;
                        }
//                         $arr[] = $EventKey; 
                        echo $this->make_xml("text",$arr); 
                        exit; 
                    }
                }elseif ($MsgType=='text'){
                    switch ($this->keyword){
                            case "1":
                                $arr[] = $this->thxgaokao;
                                break;
                            case "2":
                                $arr[] = $this->thxgaokao;
                                break;
                            case "3":
                                $arr[] = $this->thxgaokao;
                                break;
                            default:
                                $arr[] = $this->allgaokao;
                                break;
                    }
                    echo $this->make_xml("text",$arr); 
                    exit; 
                    
                }
        }else { 
            echo "this a file for weixin API!"; 
            exit; 
        } 
    } 
    
    /**
     * 
     * 高级接口----客服接口
     * 主动回复消息
     * $openid--array
     * @param unknown $openid
     * @param unknown $content
     * @param unknown $type
     * @return mixed
     */
    public function initiativeReply($openid, $content, $type){
        $access_token = $this->get_access_token();//获取access_token
        if (is_array($openid)&&is_array($content)){
            foreach ($openid as $idKey=>$idValue){
                $MsgData[]  = $this->sendKfMessage($access_token, $idValue, $content[$idKey], $type);
            }
        }else if (is_array($openid)){
            foreach ($openid as $idKey=>$idValue){
                $MsgData[]  = $this->sendKfMessage($access_token, $idValue, $content, $type);
            }
        }else {
            $MsgData    =   $this->sendKfMessage($access_token, $openid, $content, $type);
        }
        return $MsgData;
    }
    
    /**
     * 高级接口
     * 模板消息
     * @param unknown $access_token
     * @param unknown $openid
     * @param unknown $template_id
     * @param unknown $url
     * @param unknown $User
     * @param unknown $Date
     * @param unknown $CardNumber
     * @param unknown $Type
     * @param unknown $Money
     * @param unknown $DeadTime
     * @param unknown $DeadTime
     * @param unknown $Left
     * @return mixed
     */
    public function sendTemplateMessage($openid , $messageurl , $title , $datetime ,$name , $content , $messageone , $onenum , $messagetwo , $twonum){
        $access_token = $this->get_access_token();//获取access_token
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
        $Message = array(
            "touser"    =>  $openid,
            "template_id"   =>  "UIAaYbZZqwPz_Y0jve9aJlKvLCYMxjjTx98D8TgSIU4",
            "url"       =>  urlencode($messageurl),
            "topcolor"  =>  "#FF0000",
            "data"      =>  array(
                "name"    =>array(
                    "value" =>  urlencode($name),
                    "color" =>  "#2da4de"
                ),
                "content"  =>  array(
                    "value" =>  urlencode($content),
                    "color" =>  "#000000"
                ),
                "messageone" =>  array(
                    "value" =>  urlencode($messageone),
                    "color" =>  "#000000"
                ),
                "onenum"  =>array(
                    "value" =>  urlencode($onenum),
                    "color" =>  "#2da4de"
                ),
                "messagetwo"  =>  array(
                    "value" =>  urlencode($messagetwo),
                    "color" =>  "#000000"
                ),
                "twonum"  =>  array(
                    "value" =>  urlencode($twonum),
                    "color" =>  "#2da4de"
                ),
            ),
        );
        $jsonDate = urldecode(json_encode($Message));
        $data_json = $this->curlPost($url,$jsonDate);
        $data_array = json_decode($data_json, true);
        return $data_array;
    }
    
    
    
    /**
     * 多媒体上传
     * @return mixed
     */
    public function uploadMedia($filename,$type="image"){
        $access_token = $this->get_access_token();//获取access_token
        $url ="http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$access_token."&type=".$type;
        $post['media'] = '@'.$filename;
        if (is_array($post)) {
            foreach ($post as $key => $val) {
                $encode_key = $key;
                if ($encode_key != $key) {
                    unset($post[$key]);
                }
                $post[$encode_key] = $val;
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $data_array = json_decode($response, true);
        return $data_array;
    }
    
    
    
    /**
     * 客服接口---发送客服消息
     */
    public function sendKfMessage($access_token , $openid , $content , $type){
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
        $message = array(
            "touser"    => $openid,
            "msgtype"   => "text",
            "text"      => array(
                "content"   => urlencode($content),
            ),
        );
        $jsonDate = urldecode(json_encode($message));
        $data_json = $this->curlPost($url,$jsonDate);
        $data_array = json_decode($data_json, true);
        return $data_array;
    }
    
    
    /**
     * 获取客服列表
     * @param unknown $access_token
     * @return string
     */
    public function getKfList($access_token){
        $url = "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=".$access_token;
        $data = file_get_contents($url);
        return $data;
    }
    /**
     * 设置客服帐号的头像
     * @param unknown $access_token
     * @return string
     */
    public function setKfAvatar($access_token , $kfaccout){
        $url = "http://api.weixin.qq.com/customservice/kfaccount/uploadheadimg?access_token=".$access_token.'&kf_account='.$kfaccout;
//头像未完成         
        $accout_json = $this->curlPost($url);
        $accout_array = json_decode($accout_json, true);
        return $accout_array;
    }
    /**
     * 添加客服帐号
     * @param unknown $access_token
     * @param unknown $kfaccout
     * @param unknown $kfname
     * @param unknown $kfpw
     * @return mixed
     */
    public function addKfAccout($access_token , $kfaccout , $kfname , $kfpw){
        $access_token = $this->get_access_token();//获取access_token 
        $url = "https://api.weixin.qq.com/customservice/kfaccount/add?access_token=".$access_token;
        $accout = array(
            "kf_account" => $kfaccout,
            "nickname"   => urlencode($kfname),
            "password"   => $kfpw,
        );
        $jsonDate = urldecode(json_encode($accout));
        $accout_json = $this->curlPost($url,$jsonDate);
        $accout_array = json_decode($accout_json, true);
        return $accout_array;
    }
    /**
     * 修改客服帐号
     * @param unknown $access_token
     * @param unknown $kfaccout
     * @param unknown $kfname
     * @param unknown $kfpw
     * @return mixed
     */
    public function editKfAccout($access_token , $kfaccout , $kfname , $kfpw){
        $url = "https://api.weixin.qq.com/customservice/kfaccount/update?access_token=".$access_token;
        $accout = array(
            "kf_account" => $kfaccout,
            "nickname"   => urlencode($kfname),
            "password"   => $kfpw,
        );
        $jsonDate = urldecode(json_encode($accout));
        $accout_json = $this->curlPost($url,$jsonDate);
        $accout_array = json_decode($accout_json, true);
        return $accout_array;
    }
    /**
     * 删除客服帐号
     * @param unknown $access_token
     * @param unknown $kfaccout
     * @param unknown $kfname
     * @param unknown $kfpw
     * @return mixed
     */
    public function delKfAccout($access_token , $kfaccout , $kfname , $kfpw){
        $url = "https://api.weixin.qq.com/customservice/kfaccount/del?access_token=".$access_token;
        $accout = array(
            "kf_account" => $kfaccout,
            "nickname"   => urlencode($kfname),
            "password"   => $kfpw,
        );
        $jsonDate = urldecode(json_encode($accout));
        $accout_json = $this->curlPost($url,$jsonDate);
        $accout_array = json_decode($accout_json, true);
        return $accout_array;
    }
    
    
    /** 
     * 获取access_token 
     */ 
    private function get_access_token() 
    { 
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->app_id."&secret=".$this->app_secret; 
        $data = json_decode(file_get_contents($url),true); 
        if($data['access_token']){ 
            return $data['access_token']; 
        }else{ 
            return "获取access_token错误"; 
        } 
    } 
    /** 
     * 创建菜单 
     * @param $access_token 已获取的ACCESS_TOKEN 
     */ 
    public function createmenu() {
        $access_token = $this->get_access_token();//获取access_token  
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token; 
        $arr = array(  
            'button' =>array( 
                array( 
                    'name'=>urlencode("课程定制"), 
                    'sub_button'=>array( 
                        array( 
                            'name'=>urlencode("约课官网"), 
                            'type'=>'view', 
                            'url'=>'http://17yueke.cn'
                        ), 
                        array( 
                            'name'=>urlencode("发布心愿单"), 
                            'type'=>'view', 
                            'url'=>'http://17yueke.cn'
                        ), 
                        array( 
                            'name'=>urlencode("跟约秘笈"), 
                            'type'=>'view', 
                            'url'=>'http://create.maka.im/k/TELCAEJU'
                        ) 
                    ) 
                ), 
                array( 
                    'name'=>urlencode("找好培训"), 
                    'sub_button'=>array( 
                        array( 
                            'name'=>urlencode("生活兴趣"), 
                            'type'=>'view', 
                            'url'=>'http://www.17zuke.com/home/index/select/cateid//s/6.html'
                        ), 
                        array( 
                            'name'=>urlencode("音乐培训"), 
                            'type'=>'view', 
                            'url'=>'http://www.17zuke.com/home/index/select/cateid/2.html'
                        ) , 
                        array( 
                            'name'=>urlencode("职业考证"), 
                            'type'=>'view', 
                            'url'=>'http://www.17zuke.com/home/index/select/cateid/3.html'
                        ) , 
                        array( 
                            'name'=>urlencode("设计培训"), 
                            'type'=>'view', 
                            'url'=>'http://www.17zuke.com/home/index/select/cateid/4.html'
                        ) , 
                        array( 
                            'name'=>urlencode("外语培训"), 
                            'type'=>'view', 
                            'url' =>'http://www.17zuke.com/home/index/select/cateid/1.html'
                        ) 
                    ) 
                ), 
                array( 
                    'name'=>urlencode("个人中心"), 
                    'sub_button'=>array( 
                        array( 
                            'name'=>urlencode("个人主页"), 
                            'type'=>'view', 
                            'url'=>'http://17yueke.cn/home/Wx/myInfo' 
                        ), 
                        array( 
                            'name'=>urlencode("课程动态"), 
                            'type'=>'view', 
                            'url'=>'http://17yueke.cn/home/Wx/course' 
                        ), 
                        array( 
                            'name'=>urlencode("修改密码"), 
                            'type'=>'view', 
                            'url'=>'http://17yueke.cn/home/Wx/resetpw' 
                        ), 
                        array( 
                            'name'=>urlencode("领取奖学金"), 
                            'type'=>'click', 
                            'key'=>'scholarship' 
                        ), 
                        array( 
                            'name'=>urlencode("意见反馈"), 
                            'type'=>'click', 
                            'key'=>'feedback' 
                        ) 
                    ) 
                ) 
            ) 
        );
        $jsondata = urldecode(json_encode($arr)); 
        $ch = curl_init(); 
        curl_setopt($ch,CURLOPT_URL,$url); 
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
        curl_setopt($ch,CURLOPT_POST,1); 
        curl_setopt($ch,CURLOPT_POSTFIELDS,$jsondata); 
        curl_exec($ch); 
        curl_close($ch); 
        return true;
    } 
    /** 
     * 查询菜单 
     * @param $access_token 已获取的ACCESS_TOKEN 
     */ 
     
    private function getmenu($access_token) 
    { 
        # code... 
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$access_token; 
        $data = file_get_contents($url); 
        return $data; 
    } 

    /****************************************************
     *  获取第三方自定义菜单
     ****************************************************/
    public function wxMenuGetInfo($access_token){
        $url            = "https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=" . $access_token;
        $data = file_get_contents($url); 
        return $data; 
    }
    
    /** 
     * 删除菜单 
     * @param $access_token 已获取的ACCESS_TOKEN 
     */ 
     
    private function delmenu($access_token) 
    { 
        # code... 
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$access_token; 
        $data = json_decode(file_get_contents($url),true); 
        if ($data['errcode']==0) { 
            # code... 
            return true; 
        }else{ 
            return false; 
        } 
    } 
    //获取开发者的创建的菜单
    public function infomenu(){
        $access_token = $this->get_access_token();//获取access_token
        $infoinfo = $this->getmenu($access_token);
        return $infoinfo;
    }
    //获取公众平台创建的菜单
    public function infomenuauto(){
        $access_token = $this->get_access_token();//获取access_token
        $infoinfo = $this->wxMenuGetInfo($access_token);
        return $infoinfo;
    }
//------------------------------------卡券---------------------------------------------------------  

    /**
     * 查看对应卡券的code
     * @param unknown $access_token
     * @param unknown $cardId
     * @param string $code
     * @return mixed
     */
    public function getCodeCard( $access_token , $cardId , $code='' ){
        $url = 'https://api.weixin.qq.com/card/code/get?access_token='.$access_token;
        $post_code = array(
            'card_id'   =>  $cardId,
        );
        if (!empty($code)){
            $post_code['code'] = $code;
        }
        $jsonDate = json_encode($post_code);
        $data_json = $this->curlPost($url,$jsonDate);
        $data_array = json_decode($data_json, true);
        return $data_array;
    }
    
    /**
     * 查看卡券的详情
     * @param unknown $access_token
     * @param unknown $cardId
     * @return mixed
     */
    public function getBaseInfoCard( $access_token , $cardId ){
        $url = 'https://api.weixin.qq.com/card/get?access_token='.$access_token;
        $post_base = array(
            'card_id'   =>  $cardId,
        );
        $jsonDate = json_encode($post_base);
        $data_json = $this->curlPost($url,$jsonDate);
        $data_array = json_decode($data_json, true);
        return $data_array;
    }
    /**
     * 获取卡卷列表
     * @param unknown $access_token
     * @return mixed
     */
    public function getcard($access_token){
//         $access_token = $this->get_access_token();//获取access_token
        $url = 'https://api.weixin.qq.com/card/batchget?access_token='.$access_token;
        $postdata = array(
            'offset'    =>  0,
            'count'     =>  700,
        );
        $jsonDate = json_encode($postdata);
        $data_json = $this->curlPost($url,$jsonDate);
        $data_array = json_decode($data_json, true);
        return $data_array;
    }
    /**
     * 获取投放卡卷id的code信息
     * @param unknown $access_token
     * @param unknown $cardId
     * @return mixed
     */
    private function pushcard( $access_token , $cardId ){
        
//         $access_token = $this->get_access_token();//获取access_token
        $url = 'https://api.weixin.qq.com/card/code/get?access_token='.$access_token;
        $message = array(
            'card_id'   =>  'peh6Mt7XzK9nlVBfjV4XQ1BSIxkk',
        );
        $jsonDate = json_encode($message);
        $data_json = $this->curlPost($url,$jsonDate);
        $data_array = json_decode($data_json, true);
        return $data_array;
    }
    /**
     * 投放卡券信息到用户上
     * @return mixed
     */
    public function toucard(){
        $access_token = $this->get_access_token();//获取access_token
        
        $data_array = $this->getcard($access_token);
        if ($data_array['errcode']!=0){
            return $data_array['errcode'];
        }
        $cardListArray = $data_array['card_id_list'];
        $reverse_array = array_reverse($cardListArray);
        $cardId = $reverse_array[0];
        //获取卡券详情
        $code_info = $this->getBaseInfoCard( $access_token, $cardId );
        return $code_info;
        
        
        //客服发卡券
        $message = array(
            "touser"    =>  $this->fromUsername,
            "msgtype"    =>  "wxcard",
            "wxcard"    =>  array(
                "card_id"   =>  $cardId,
                "card_ext"  =>  "xxxxxxxxxxxxxxxxxxx",
            ),
        );
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
        $jsonDate = json_encode($message);
        $data_json = $this->curlPost($url,$jsonDate);
        $data_array = json_decode($data_json, true);
        return $data_array;
        
        
        $carddata = $this->pushcard($access_token,$cardId);
        $value_arr = array(
            0   =>  '奖学金',
            1   =>  $carddata['content'],
            2   =>  'http://17yueke.cn/Public/Desktop/img/c_phone.png',
            3   =>  'http://17yueke.cn/',
        );
        return json_encode($carddata, true);
        return $this->make_xml('news', $value_arr);
    }
    /*******************************************************
     *      微信卡券：上传LOGO - 需要改写动态功能
     *******************************************************/
    public function wxCardUpdateImg($access_token) {
        $data['buffer']     =  '@http://17yueke.cn/Public/Home/img/iPhone.png';
        $url            = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=".$access_token;
//         $json_data      = json_encode($data);
        $result         = $this->curlPost($url,$data);
        $jsoninfo       = json_decode($result, true);
        return $jsoninfo;
        //array(1) { ["url"]=> string(121) "http://mmbiz.qpic.cn/mmbiz/ibuYxPHqeXePNTW4ATKyias1Cf3zTKiars9PFPzF1k5icvXD7xW0kXUAxHDzkEPd9micCMCN0dcTJfW6Tnm93MiaAfRQ/0" }
    }
     
    /*******************************************************
     *      微信卡券：获取颜色
     *******************************************************/
    public function wxCardColor($access_token){
        $url                = "https://api.weixin.qq.com/card/getcolors?access_token=".$access_token;
        $result         = $this->curlGet($url);
        $jsoninfo       = json_decode($result, true);
        return $jsoninfo;
    }
     
    /*******************************************************
     *      微信卡券：创建卡券
     *******************************************************/
    public function wxCardCreated($access_token) {
        //创建卡券
        $arrayData = array(
            'card'  =>  array(
                'card_type' =>  'CASH',
                'cash'      =>  array(
                    'least_cost'    =>  0,
                    'reduce_cost'   =>  1000,
                    'base_info'     =>  array(
                        'logo_url'  =>  urlencode('https://mmbiz.qlogo.cn/mmbiz/VricdB1tzUmb2IycY6SdMIjwQ0T2FXCL4QGTmUia6Q1DjibGgmXn0wD9nvFz0AofjYzgVaFZViaXaVbiaGfyjJ1k6Wg/0?wxfmt=png'),
                        'code_type' =>  'CODE_TYPE_QRCODE',
                        'brand_name'=>  urlencode('17约课'),
                        'title'     =>  urlencode('奖学金10元'),
                        'sub_title' =>  urlencode('报班减免相应学费'),
                        'color'     =>  urlencode('Color030'),
                        'notice'    =>  urlencode('报班参加培训时出示此券'),
                        'description'   =>  urlencode('17约课，一起约上课！ 上www.17yueke.cn，找到适合的课程，约老师约男神约女神！ 报名课程后，到任何入驻17约课的教育机构报班，出示此券即可减免学费！'),
                        'sku'       =>  array(
                            'quantity'  =>  10,
                        ),
                        'date_info' =>  array(
                            'type'  =>  2,
                            'fixed_term'   =>  30,
                            'fixed_begin_term'  =>  0,
                        ),
                        'use_custom_code'   =>  false,
                        'bind_openid'   =>  false,
                        'service_phone' =>  '020-28107517',
                        'location_id_list'  =>  array(
                            0   =>  286255324,
                        ),
                        'custom_url_name'   =>  urlencode('立即使用'),
                        'get_limit' =>  1,
                        'can_share' =>  true,
                        'can_give_friend'   =>  true,
                    ),
                ),
            ),
        );
        $jsonData = urldecode(json_encode($arrayData));
        $url            = "https://api.weixin.qq.com/card/create?access_token=" . $access_token;
        $result         = $this->curlPost($url,$jsonData);
        $jsoninfo       = json_decode($result, true);
        return $jsoninfo;
    }

    /****************************************************
     *  微信提交API方法，返回微信指定JSON
     ****************************************************/
    
    public function wxHttpsRequest($url,$data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
//------------------卡券------------------------------------------------------------    
    
    
    /** 
     *@param type: text 文本类型, news 图文类型 
     *@param value_arr array(内容),array(ID) 
     *@param o_arr array(array(标题,介绍,图片,超链接),...小于10条),array(条数,ID) 
     */ 
    private function make_xml($type,$value_arr,$o_arr=array(0)){ 
        //=================xml header============ 
        $con="<xml> 
                    <ToUserName><![CDATA[{$this->fromUsername}]]></ToUserName> 
                    <FromUserName><![CDATA[{$this->toUsername}]]></FromUserName> 
                    <CreateTime>{$this->times}</CreateTime> 
                    <MsgType><![CDATA[{$type}]]></MsgType>"; 
                     
          //=================type content============ 
        switch($type){ 
           
            case "text" :  
                $con.="<Content><![CDATA[{$value_arr[0]}]]></Content> 
                    <FuncFlag>{$o_arr}</FuncFlag>";   
            break; 
             
            case "news" :  
                $con.="<ArticleCount>{$o_arr[0]}</ArticleCount> 
                     <Articles>"; 
                foreach($value_arr as $id=>$v){ 
                    if($id>=$o_arr[0]) break; else null; //判断数组数不超过设置数 
                    $con.="<item> 
                         <Title><![CDATA[{$v[0]}]]></Title>  
                         <Description><![CDATA[{$v[1]}]]></Description> 
                         <PicUrl><![CDATA[{$v[2]}]]></PicUrl> 
                         <Url><![CDATA[{$v[3]}]]></Url> 
                         </item>"; 
                } 
                $con.="</Articles> 
                     <FuncFlag>{$o_arr[1]}</FuncFlag>";   
            break; 
        } //end switch 
         //=================end return============ 
        $con.="</xml>"; 
        return $con; 
    } 
    
    
    
    
    
    /**
     * curl---get获取数据
     * @param unknown $url
     * @return mixed
     */
    public function curlGet($url){
        $ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $temp = curl_exec($ch);
        return $temp;
        
    }
    /**
     * curl---post提交数据
     * @param unknown $customMessageSendUrl
     * @param unknown $postJosnData
     * @return mixed
     */
    public function curlPost($customMessageSendUrl,$postJosnData){
        $ch = curl_init($customMessageSendUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postJosnData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
    
    
    
  /**
   * 验证-网站的--token
   * @return boolean
   */
    private function checkSignature() 
    { 
        $signature = $_GET["signature"]; 
        $timestamp = $_GET["timestamp"]; 
        $nonce = $_GET["nonce"];     
                 
        $token = TOKEN; 
        $tmpArr = array($token, $timestamp, $nonce); 
        sort($tmpArr); 
        $tmpStr = implode( $tmpArr ); 
        $tmpStr = sha1( $tmpStr ); 
         
        if( $tmpStr == $signature ){ 
            return true; 
        }else{ 
            return false; 
        } 
    } 
} 
