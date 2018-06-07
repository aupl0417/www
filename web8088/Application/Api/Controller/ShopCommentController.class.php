<?php

namespace Api\Controller;
use Api\Controller\CommonController;
use Common\Model\ShopCommentModel;

/**
 * @author JM_Joy
 */
class ShopCommentController extends CommonController {

    /**
     * 商家组团信息评论模型
     * @var ShopCommentModel
     */
    public $shopCommentModel;

    /**
     * 初始化
     */
    public function _initialize() {
        $this->shopCommentModel = D('Common/ShopComment');
    }

    /**
     * POST：处理添加请求，
     * 接收：iid（组团信息的ID），content(评论内容)
     */
    public function handleAdd() {
        $result = $this->shopCommentModel->handleAdd();
        $this->simpleAjaxReturn($result);
    }

    /**
     * GET：分页获取评论，
     * 接收：commentid（上一级评论的ID）， page（第几页）
     */
    public function listComment() {
        $resArr = $this->shopCommentModel->listComment();
        $this->ajaxReturn(array(
                'status'	=>	200,
                'msg'		=>	'',
                'data'		=>	$resArr,
        ));
    }

    /**
     * 根据课程id查询评论
     * @param number $id
     * @param number $page
     */
    public function listUserOrShopComment($id = 0, $page = 1) {
        $perPage =  5;

        $totalRow = $this->shopCommentModel->countCommentByInfoId($id);
        $resArr = $this->shopCommentModel->listUserOrShopComment(
                $id, $page, $perPage, session('user.id'), session('shopkeeper.id')
        );
        foreach ($resArr as $key => $value) {
            $resArr[$key]['ctime'] = transDate($value['ctime']);
        }

        $isLast = $this->isLastPage($page, $perPage, $totalRow);

        $this->ajaxReturn(array(
                'data'		=>	$resArr,
                'isLast'	=>	$isLast,
        ));
    }

    /**
     * 提交一条评论
     * @param number $id 课程信息的ID
     * @param string $content
     */
    public function postComment() {
        // 依据有没有登录判断是谁作出的评论
//         if (!session('?user.id') && !session('?shopkeeper.id')) {
//             return $this->ajaxReturn([
//                 'status'    =>  403,
//                 'msg'       =>  '请先登录',
//             ]);
//         }
        //游客id
        $visitor_id=I('post.visitorid')?I('post.visitorid'):0;

        if (session('user.id')) {  // 这个是用户评论的，判断用户的操作权限
            $result = $this->checkUserPermisson();
            if ($result !== true) {
                return $this->ajaxReturn([
                    'status'    =>  403,
                    'msg'       =>  $result,
                ]);
            }

        } else if (session('shopkeeper.id')) {  // 这个是商家评论的，判断商家的操作权限
            $result = $this->checkShopkeeperPermission();
            if ($result !== true) {
                return $this->ajaxReturn([
                    'status'    =>  403,
                    'msg'       =>  $result,
                ]);
            }

        }elseif (!session('?user')){
//--------------------游客跟约处理------------------------------------------------------------------------------------------
            if ($visitor_id!=0&&!session('?visitor')){
                session('visitor.id',$visitor_id);
            }
            if (!session('?visitor')){
                $visitor = D('Visitor');
                $addOneId = $visitor->addOneVisitor();//增加一个游客
                session('visitor.id',$addOneId);
            }else {
                $addOneId=session('visitor.id');
            }
            // 获取输入
            $content = I('post.content');
            $iid = I('post.id', 0, 'intval');
            $uid = intval(session('user.id'));
            $sid = intval(session('shopkeeper.id'));
            $parent_id = I('post.parent_id', 0, 'intval');
            
            $result = $this->shopCommentModel->addShopComByVisitor($content,$iid,0,0,$parent_id,$addOneId);
            
            
            
            

            if (!is_array($result)) {
                return $this->simpleAjaxReturn($result);
            }
            
            $visitinfo = D('Visitor')->getOneInfo($addOneId);
            $visit_def_avatar=C('visitor_config')['avatar'];
            
            // 组装数据
            $value['isMe'] = true;
            $value['content'] = $content;
            $value['uid'] = 0;
            $value['sid'] = 0;
            $value['id'] = $result['lastId'];
            $value['parent_nickname']= $result['parent_nickname'];
            $value['vid']=$addOneId;
            $value['nickname']=$visitinfo['name'];
            $value['avatar']=$visit_def_avatar;
            
            $this->ajaxReturn([
                'status'    =>  200,
                'msg'       =>  '评论成功',
                'data'      =>  [$value],
            ]);
            
            
    
            
     
//--------------------游客跟约处理-------------------------------------------------------------------------------------------
        }else {
            $this->ajaxReturn(array(
                'status'    =>  403,
                'msg'       =>  '请先登录',
            ));
        }

        // 获取输入
        $content = I('post.content');
        $iid = I('post.id', 0, 'intval');
        $uid = intval(session('user.id'));
        $sid = intval(session('shopkeeper.id'));
        $parent_id = I('post.parent_id', 0, 'intval');

        $result = $this->shopCommentModel->postComment(
            $content, $iid, $uid, $sid, $parent_id
        );

        if (!is_array($result)) {
            return $this->simpleAjaxReturn($result);
        }

        $value = $this->shopCommentModel->getCommenterInfo(
            session('user.id'), session('shopkeeper.id')
        );

        // 组装数据
        $value['isMe'] = true;
        $value['content'] = $content;
        $value['uid'] = $uid;
        $value['sid'] = $sid;
        $value['id'] = $result['lastId'];
        $value['parent_nickname']= $result['parent_nickname'];

        $this->ajaxReturn([
            'status'    =>  200,
            'msg'       =>  '评论成功',
            'data'      =>  [$value],
        ]);
    }

    /**
     * 删除那一条评论
     */
    public function deleteComment() {
        // 依据有没有登录判断是谁作出的评论
        if (!session('?user.id') && !session('?shopkeeper.id')) {
//-----------------------------------------游客--------------------------------------------------------
            $comid=I('post.id', 0, 'intval');
            $visitorid=I('post.visitorid', 0, 'intval')?I('post.visitorid', 0, 'intval'):0;
            if (!session('?visitor')&&$visitorid==0){
                $this->simpleAjaxReturn('非法操作');
            }
            if (session('?visitor')){
                $visitorid=session('visitor.id');
            }
            if (!$visitorid){
                $this->simpleAjaxReturn('非法操作');
            }
            $result = $this->shopCommentModel->deleteByvisitorid($comid,$visitorid);
            $this->simpleAjaxReturn($result);
//-----------------------------------------游客--------------------------------------------------------
//             return $this->simpleAjaxReturn('请先登录');
        }

        $result = $this->shopCommentModel->deleteById(
            I('post.id', 0, 'intval'),
            session('user.id'), session('shopkeeper.id')
        );

        $this->simpleAjaxReturn($result);
    }

    /**
     * 看看评论有没有更新啊
     * @param number $id
     * @param number $time
     */
    public function numUpdate() {
        // 看商家有没有登录
        $this->checkShopSignIn();

        $result = $this->shopCommentModel->numUpdate(session('shopkeeper.id'));

        if (!is_int($result)) {
            return $this->ajaxReturn([
                    'status'	=>	400,
                    'msg'		=>	$result,
            ]);
        }

        $this->ajaxReturn([
                'status'	=>	200,
                'data'		=>	$result,
        ]);
    }

}
