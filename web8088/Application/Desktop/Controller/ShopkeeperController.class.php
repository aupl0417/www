<?php

namespace Desktop\Controller;
use Common\Controller\BaseController;

class ShopkeeperController extends BaseController {

    /**
     * 列出文件
     */
    public function show() {
        $path = realpath(dirname(__DIR__) . '/View/Shopkeeper/');
        foreach (scandir($path) as $filename) {
            $filepath = $path . '/' . $filename;
            if (is_file($filepath)) {
                $link = __CONTROLLER__ . '/' . $filename;
                echo "<h3><a href=\"$link\">$link</a></h3>";
            }
        }
    }

    /**
     * 首页
     */
    public function index() {
       $this->cateNav();

        // 热门课程
        $this->gener = D('Common/ShopInfo')->gener(3, true);

        // 合作机构
        $this->shname = D('Common/ShopkeeperDetail')->getShopName(0, 24);
        
        $this->display();
    }
    
    //商家资料编辑
    public function edit(){
        //判定商家是否登录
//         $this->isLogin();
        if (!session('?shopkeeper.id')) {
//             return $this->display('s_sign_timeout_loc');
               echo '<script>alert("请登录");</script>';
               $jumpUrl = U('Pc/index', '', 0);
               $this->redirect($jumpUrl);
        }
        $this->shopkeeperModel = D("Common/Shopkeeper");
        $resArr = D("Common/ShopkeeperDetail")->info(session('shopkeeper.id'), false);
        
        // 先计个分再说
        //$resArr['credit'] =$this->sumCreidt($resArr);
        $resArr['credit'] = $this->shopkeeperModel->sumCreidt(session('shopkeeper.id'));
        
        // 如果商家没有上传头像，就给他默认头像
        $resArr['avatar'] = $this->getAvatar($resArr['avatar']);
        $resArr['avatar'] = str_replace("//", '/', $resArr['avatar']);

        // 判断场景图是不是默认的，如果是默认的就删掉
        if ($resArr['environ'] == 'shop_environ/shop_default_environ.jpg') {
            $resArr['environ'] = '';
        }
        // 特性字符串
        if (!$resArr['features']) {
            $resArr['features'] = '';
        }
        $resArr['featureArr'] = $this->getFeaturesArr($resArr['features']);
        
        // 获取所有与areaid同级的地区
        if ($resArr['areaid']) {
            $this->allArea = D("Common/Area")->getSameLevel($resArr['areaid']);
            $this->areaPid = D("Common/Area")->getParentId($resArr['areaid']);
        } else {
            $this->areaPid = 0;
        }
        //父类catename
        $this->parentCateName = $this->getParentCateName($resArr['cateid']);
        
        // 获取所有广州地区
        $this->allZone = D("Common/Area")->getGuangZhouZone();
        
        // 判断是否填写了分类，需求是填写过分类下次就不给改了
        // $this->category = M('Category')->field('id, catename')->select();
        $this->allCategory = D('Common/Category')->getAllTwoLevel();
        foreach($this->allCategory as $key=>$value) {
            foreach ($value as $k=>$v) {
                if($v['parent_id'] != 0){
                    $childCategory[$key][$k] = $v;
                }else {
                    $parentCategory[$key][$k] = $v;
                }
            }
        }
        $this->parentCategory = $parentCategory;
        $this->childCategory = $childCategory;
        
        // 显示
        $this->resArr = $resArr;
       
        $this->display();
    }
   
    /**author : aupl
     * 获取cateid父类cate的名字
     * */
    protected function getParentCateName($cateid){
        $table = 'ls_category sa,ls_category sb';
        $field = 'sb.catename';
        $where = 'sa.parent_Id=sb.Id and sa.Id='.$cateid;
        return  M()->table($table)->where($where)->getField($field);
    }
    
    //发布课程
    public function publish () {
        // 获取所有广州地区
        $this->allZone = D("Common/Area")->getGuangZhouZone();
        //获取每个区里所有的街道
        $allArea = array();
        foreach($this->allZone as $k=>$v) {
            $allArea[$v['areaname']] = D("Common/Area")->getByParentId($v['id']);
        }
        $this->allArea = $allArea;
        
        //课程类别
        $this->category = D('Common/Category')->getAllCateInfo();
        
        $this->display();
    }
    
    //求学意向
    public function intention() {
        $this->display();
    }
    
    //消息
    public function message() {
        $this->display();
    }
    /**
     * 商家认证
     */
    public function certification() {
        
        //判定是否登录
//        $this->isLogin();
           
        // 取出闪存数据
        $err['company_name'] = cookie('flash_v_company_name');
        $err['legal_name'] = cookie('flash_v_legal_name');
        $err['tel'] = cookie('flash_v_tel');
        $err['msg'] = cookie('flash_v_msg');
        // 删除闪存数据
        cookie('flash_v_company_name', null);
        cookie('flash_v_legal_name', null);
        cookie('flash_v_tel', null);
        cookie('flash_v_msg', null);

        $this->err = $err;

        $this->display();
    }

    /**
     * 课程详细信息页面
     */
    public function course($id = 0) {
        if (!$id) {
            die('Access Deny!');
            // return $this->redirect("Index/notFound");
        }
		$resArr = D('Common/ShopInfo')->listInfo(0, 1, 'desc', $id, null, true);
		if ($resArr === null) {
            die('Access Deny!');
            // return $this->redirect("Index/notFound");
		}
		$resArr = $resArr[0];

		// 获取额外的信息
		$resArr['ctime'] = transDate($resArr['ctime']);
		$resArr['avatar'] = $this->getAvatar($resArr['avatar']);

		// 每次访问 +1
		M('ShopInfo')->where('id = %d', $id)->setInc('view');

        // 看看用户是否已经收藏或者报名
        $isUserEnroll = '';
        $isUserStar = '';
        if (session('?user')) {
            $isUserStar = M('UserCollect')->where('shopid = %d and uid = %d', $id, session('user.id'))
                                            ->getField('id');

            $isUserEnroll = M('ShopInfoUser')->where('shop_info_id = %d and user_id = %d', $id, session('user.id'))
                                            ->getField('id');
        }

        // 商家详细信息
    	$resArr['shopDetail'] = D("Common/ShopkeeperDetail")->info($resArr['sid']);

        // 商家已发布课程
        $resArr['hotInfo'] = D('Common/ShopInfo')->getSimpleInfo($resArr['sid'], 1, 2);

        // 课程信息数据
		$this->resArr = $resArr;

        // 判断用户是否登录
        $this->isUserSignIn = session('?user.id');
        // 判断用户是否报名了
        $this->isUserEnroll = $isUserEnroll;
        // 判断用户是否收藏了
        $this->isUserStar = $isUserStar;
        // 判断用户是否有头像
        // 获取用户的分数和是否有头像
        if (session('?user.id')) {
            $this->userScore = D('Common/User')->userScore();
            $this->isUserHasAvatar = D('Common/User')->userAvatars(session('user.id'));
        } else {
            $this->userScore = 0;
            $this->isUserHasAvatar = "";
        }

        //----游客头像
        $this->visitor_avatar=C('visitor_config')['avatar'];
        //----商家是否登录了
        $this->isShopkeepSignIn=session('?shopkeeper.id');


		$this->display();
    }

    /**
     * 查看商家发布的课程
     */
    public function published() {
        //判定商家是否登录
//         $this->isLogin();
        // 看是不是我啊
        $isMe = false;

        // 是我了
        if (session('?shopkeeper') && session('shopkeeper.id') == $id) {
            $isMe = true;
        }

        $this->isMe = $isMe;
        $this->sid = $id;
        $this->display();
    }

    /**
     * 选择（搜索）课程
     */
    public function course_select() {
        $this->cateNav();
        // 获取输入
        $this->areaid = I('get.areaid', 0, 'intval');
        $this->cateid = I('get.cateid', 0, 'intval');
        $this->searchType = I('get.searchType', '');    //$this->searchType : wish or course
        $this->greet = I('get.greet', '');         //$this->greet : greet
        $this->keyword = I('get.searchWord');
        
        $order = ($this->greet == 'greet') ? 'number' : 'Id';
        //echo $order;
        if(!empty($this->keyword)){
            if($this->searchType == 'course'){
                $Arrid = D('Common/ShopInfo')->getAreaidAndCateid($this->keyword);
            }else {
               // $Arrid = 
            }
            
            foreach($Arrid as $k=>$v) {
                // 处理地区
                if ($v['areaid']) {
                    $this->areaArr = D('Common/Area')->getGuangZhouAllZone();
                    // 获取当前Area的Key
                    foreach ($this->areaArr as $key => $row) {
                        if ($row['item']['id'] == $v['areaid']) {
                            $this->currentAreaKey = $key;
                            break;
                        }
                        foreach ($row['sub'] as $subRow) {
                            if ($subRow['id'] == $v['areaid']) {
                                $this->currentAreaKey = $key;
                                break 2;
                            }
                        }
                    }
                }
                //  处理分裂
                if ($v['cateid']) {
                    $this->cateArr = D('Common/Category')->getThreeLevelByLastId($v['cateid']);
                }
                $info[$k] = D('Common/ShopInfo')->getCourseInfo(0,3,$v['cateid'], $order);
                
            }
            //var_dump($info);
            $this->info = array('info'=>$info);
        }
         
        $this->display();
    }

    /**
     * 系统页面
     */
    public function system() {
        //判定商家是否登录
//         $this->isLogin();
        $this->cateNav();

        $this->display();
    }

    /**
     * 商家个人中心
     */
    public function home() {
        //判定商家是否登录
//         $this->isLogin();
        if (!$id = I('get.id', 0, 'intval')) {
//             if (!session('?shopkeeper.id')) {
//                 return $this->display('s_sign_timeout_loc');
//             }
//             $id = session('shopkeeper.id');
        }

        $resArr = D("Common/ShopkeeperDetail")->info($id);
        // 如果商家没有上传头像，就给他默认头像
        $resArr['avatar'] = $this->getAvatar($resArr['avatar']);
        $resArr['features'] = $this->getFeaturesArr($resArr['features']);
        // 显示
        $this->resArr = $resArr;
        $this->display();
    }
    
    public function logout(){
        if(session('?shopkeeper.id')){      
            session('shopkeeper', null);   
		    cookie('shop_auto_login', null);
                 
        }
        $this->redirect('Pc/index');
    }

    /**
     * 分类导航栏使用
     * 全部课程分类
     */
    protected function cateNav() {
        $cate = D('Category');
        $data = $cate->select();
        $data = $cate->getCateNav( $data );

        $this->cateNavData = $data;
    }

	/**
	 * 如果商家没有上传头像，就给他默认头像
	 * @param unknown $avatarPath
	 * @return string
	 */
    protected function getAvatar($avatarPath) {
    	if (!$avatarPath) {
    		return C('TMPL_PARSE_STRING')['__HIMG__'] . '/shop_default_avatar.jpg';
    	}
    	return C('TMPL_PARSE_STRING')['__UPLOAD__'] . '/' . $avatarPath;
    }

	/**
	 * 将features分割成数组
	 * @param unknown $features
	 * @return string
	 */
    protected function getFeaturesArr($features) {
   		$arr  = explode('|', $features);
   		foreach ($arr as $key => $value) {
   			if ($value === "") {
   				unset($arr[$key]);
   			}
   		}
   		return $arr;
    }
    
    protected function isLogin(){
        if(session('?shopkeeper.id')){
            return true;
        }else {
            $this->redirect('Index/notfound');
        }
    }

}
