<?php

namespace Admin\Controller;
use Common\Controller\CommonController;
use Common\Model\ShopkeeperModel;
use Common\Model\ShopkeeperDetailModel;
use Common\Model\ShopkeeperAuthModel;

/**
 *
 * @author jmjoy
 *
 */
class ShopkeeperController extends CommonController {

	/**
	 * 商家模型
	 * @var ShopkeeperModel
	 */
	public $shopkeeperModel;

	/**
	 * 商家详细资料模型
	 * @var ShopkeeperDetailModel
	 */
	public $shopkeeperDetailModel;

	/**
	 * 商家审核资料模型
	 * @var ShopkeeperAuthModel
	 */
	public $shopkeeperAuthModel;

	/**
	 * 每页显示数量
	 * @var int
	 */
	public $rowlist = 30;

	public function _initialize() {
        parent::_initialize();
		$this->shopkeeperModel = D('Common/Shopkeeper');
		$this->shopkeeperDetailModel = D('Common/ShopkeeperDetail');
		$this->shopkeeperAuthModel = D('Common/ShopkeeperAuth');
	}

	/**
	 * 后台商家首页
	 * @param number $status
	 * @param string $order
	 */
    public function index($status = 10, $order = 'desc') {
    	// 分页展示商家信息
    	$count = $this->shopkeeperModel->getCount($status);
    	$page = new \Common\Util\BootstrapPage($count, $this->rowlist);
    	$this->page = $page->show();
    	$this->resArr = $this->shopkeeperModel->getAndPaginate(
    			$page->firstRow, $page->listRows, $status, $order
    	);
    	// 获取管理员可执行的操作
    	$this->resArr = $this->shopkeeperModel->pushOpeartePermission(
    			$this->resArr
    	);
    	// 传递参数
    	$this->status = $status;
    	$this->order = $order;
    	//
    	$this->display();
    }

    /**
     * 增加一个商家（包括邮箱地址和手机号码）
     */
    public function handleAdd() {
    	$result = $this->shopkeeperModel->addOne();
    	if ($result !== true) {
    		die($result);
    	}
    	$this->redirect('index', array('t' => time()));
    }

    /**
     * 激活某位商家
	 * @param number $status
	 * @param string $order
     */
    public function handleActive($status = 10, $order = 'desc') {
    	$this->shopkeeperModel->activeOne(I('get.id'));
    	$this->redirect('index', array('t' => time(), 'order' => $order, 'status' => $status));
    }

    /**
     * ajax检测某一字段
     */
    public function ajaxValidateField() {
    	$result = $this->shopkeeperModel->validateField(I('type'));
    	// 验证失败
    	if ($result !== true) {
    		$this->ajaxReturn(array(
    				'status'	=>	false,
    				'msg'		=>	$result,
    		));
    	}
    	// 验证OK
    	$this->ajaxReturn(array(
    			'status'	=>	true,
    	));
    }

    /**
     * 详细信息显示页面
     * @param number $sid
     */
    public function detail($sid = 0) {
    	$row = $this->shopkeeperDetailModel->getBySid($sid);

        $row['upperCatename'] = $this->getUpperCatename($row['cateid']);

        $this->row = $row;

    	$this->display();
    }

    /**
     * 详细信息显示页面
     * @param number $sid
     */
    public function shopInfoDetail($id = 0) {
    	$row = D('Common/ShopInfo')->listInfo(0, 1, 'desc', $id, null, true);

        $this->row = $row[0];

    	$this->display('shop_info_detail');
    }

    /**
     * 审核信息显示页面
     */
    public function auth($sid = 0) {
    	$this->row = $this->shopkeeperAuthModel->getBySid($sid);
    	$this->status = $this->shopkeeperModel->getStatus($sid);
    	// 获取之前的路径
    	$this->referer = I('server.HTTP_REFERER');
    	$this->display();
    }

    /**
     * 处理审核请求
     * @param number $sid
     */
    public function ajaxhandleAuth($sid = 0) {
		$result = $this->shopkeeperModel->handleAuth($sid);
		// 失败！
		if ($result !== true) {
			$this->ajaxReturn(array(
					'status'	=>	false,
					'msg'		=>	$result,
			));
		}
		// 成功！
		$this->ajaxReturn(array(
				'status'		=>	false,
		));
    }

    /**
     * 显示添加与修改商家页面
     */
    public function addAndModify() {
        $this->display('addAndModify');
    }

    /**
     * 处理添加商家的api
     */
    public function apiHandleAdd() {
        $result = D('Common/Shopkeeper')->adminAddOne();
        if ($result !== true) {
            $this->ajaxReturn([
                'status'    =>  400,
                'msg'       =>  $result,
            ]);
        }
        $this->ajaxReturn([
            'status'    =>  200,
        ]);
    }

    /**
     * 用户报名商家课程
     */
    public function enroll() {
        $this->display();
    }

    /**
     * 列出课程信息
     */
    public function listShopInfo() {
        $curPage = I('get.page', 1, 'intval');      //当前页码。   取值，从前端传过来的值,通过get来获取参数page

        $count = M('ShopInfo')->count(); // 查询总记录数
        $pages = $this->getPagination($count, $curPage, $this->rowlist);
        $data = D('Common/ShopInfo')->listInfo($pages['pageOffset'], $pages['perPage'], 'desc');

        $this->assign('data',$data);
        $this->assign('page',$pages); // 赋值分页输出

        $this->display('list_shop_info');
    }

    /**
     * 列出某条课程信息的报名用户
     */
    public function listEnroller() {
        $curPage = I('get.page', 1, 'intval');      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $shopInfoId = I('get.id', 0, 'intval');     // 课程信息的id

        $count = M('ShopInfoUser')->where('shop_info_id = %d', $shopInfoId)->count(); // 查询总记录数

    	$page = new \Common\Util\BootstrapPage($count, $this->rowlist);
    	$this->page = $page->show();

        $this->data = D('Common/ShopInfo')->getEnrollerInfo($shopInfoId, $page->firstRow, $page->listRows);

        $this->count = $count;

        $this->display('list_enroller');
    }

    /**
     * 列出某条课程信息的所有评论
     */
    public function listComment() {
        $curPage = I('get.page', 1, 'intval');      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $shopInfoId = I('get.id', 0, 'intval');     // 课程信息的id

        $count = D('Common/ShopComment')->countCommentByInfoId($shopInfoId);  // 查询总记录数

    	$page = new \Common\Util\BootstrapPage($count, $this->rowlist);
    	$this->page = $page->show();

        $this->data = D('Common/ShopComment')->listCommenterInfo($shopInfoId, $curPage, $page->listRows);

        $this->count = $count;

        $this->display('list_comment');
    }

    /**
     * 抄袭Admin\DataController\shopkeeperdata
     */
    public function findShopkeeperdata() {

        header("content-Type: text/html; charset=Utf-8");//设置字符编码
        $Shopkeeper=D('Shopkeeper');

        $curPage=I('get.p', 1, 'intval');      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
        $findType = I('get.find_type');
        $findWord = I('get.find_word');

        // 分页用的总数
        $count = $Shopkeeper->countFindShopkeeperData($findType, $findWord);

        $page = new \Common\Util\BootstrapPage($count, $this->rowlist);
        $this->page = $page->show();

        $data = $Shopkeeper->findShopkeeperData($page->firstRow, $this->rowlist, $findType, $findWord);

        $this->assign('data',$data);
        $this->count = $count;

        $this->display('Data/shopkeeperdata');
    }

    /**
     * 发送消息给商家或者查看商家的规范
     */
    public function feedback() {
        $curPage = I('get.page', 1, 'intval');      //当前页码。   取值，从前端传过来的值,通过get来获取参数page

        $count = M('ShopFeedback')->count();  // 查询总记录数

    	$page = new \Common\Util\BootstrapPage($count, $this->rowlist);
    	$this->page = $page->show();

        $this->data = D('Common/ShopFeedback')->listFeedback($curPage, $page->listRows);

        $this->count = $count;

        $this->display();
    }

    /**
     * 发送系统消息给所有人
     */
    public function sendMsg() {
        // 发送给那位商家的id，0代表发给所有商家
        $sid = I('get.sid', 0, 'intval');

        // 发给指定商家，获取他的昵称
        if ($sid) {
            $nickname = M('ShopkeeperDetail')->where('sid=%d', $sid)->getField('nickname');
        } else {
            $nickname = '所有商家';
        }

        // 模板输出变量
        $this->sid = $sid;
        $this->nickname = $nickname;

        $this->display('send_msg');
    }

    /**
     * 处理发送信息请求
     */
    public function ajaxSendMsg() {
        $result = D('Common/ShopMsg')->addMsg($_POST);
        $this->simpleAjaxReturn($result);
    }

    /**
     * 获取分页条
     */
    protected function getPagination($count, $curPage, $perPage) {
        import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
        $page = new \Common\Util\AjaxPage($count, $curPage, $perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数 $pageArray=$Page->getCounts();
        return $page->getCounts();
    }

    /**
     * 根据当前分类id获取上一级分类的名称
     */
    protected function getUpperCatename($cateid) {
        if (!$cateid) {
            return '';
        }

        return M('Category')->alias('this')
                            ->join('__CATEGORY__ upper on upper.id = this.parent_id')
                            ->where('this.id=%d', $cateid)
                            ->getField('upper.catename');
    }

}
