<?php

namespace Admin\Controller;
use Think\Page;
/**
 * 后台评论管理控制器
 */
class CommentController extends AdminController {

    /**
     * 课程管理首页
     */
    public function index(){
        $model = D('Common/TeacherComment');
        $where = array('tc_status'=>0);
        $count = $model->where($where)->count();
        
        $page = new Page($count, C('SHOW_PAGE_SIZE'));
        $limit = $page->firstRow . ',' . $page->listRows;
        $field = array('tc_id', 'ct_name', 'tc_count', 'tc_className', 'tc_classTableId', 'tc_teacherName', 'tc_content');
        $teacherCommentList = $model->getTeacherCommentDataCache($field, $where, $limit);
        
        $this->meta_title = '后台评论管理首页';
        $this->list = $teacherCommentList;
        $this->show = $page->show();
        $this->display();
    }
    
    //添加评论标签
    public function add(){
        if(IS_POST){
            $tagName = I('ct_name', '');
            if(empty($tagName)){
                $this->error('请填写标签内容');
            }
            $time = date('Y-m-d H:i:s');
            $data = array(
                'ct_name'=>$tagName,
                'ct_createTime' => $time,
                'ct_updateTime' => $time
            );
            $id = M('comment_tag')->add($data);
            if($id){
                //记录行为
                action_log('Add_Comment_Tag', 'Comment_Tag', $id, UID);
                $this->success('添加成功', U('Course/index'));
            } else {
                $this->error('添加失败');
            }
            
        } else {
            $this->meta_title = '添加评论标签';
            $this->display();
        }
    }
    
    /*
     * 学生对老师添加评论（本不属于这个Controller,暂时写在这里）
     * */
    public function addComment(){
        if(IS_POST){
            $tagId = I('tc_tagId', 0, 'intval');
            $classTableId = I('tc_classTableId', 0, 'intval');
            $teacherId = I('tc_teacherId', 0, 'intval');
            $content = I('tc_content');
            
            if(!$tagId || !$classTableId | !$teacherId || !$content){
                $this->error('参数非法');
            }
            $className = M('class_table')->join('LEFT JOIN __CLASS__ on __CLASS_TABLE__.cta_classId=__CLASS__.cl_id')
                        ->where(array('cta_id'=>$classTableId, 'cta_teacherId'=>$teacherId))->getField('cl_name');
            if(!$className){
                $this->error('课时对应的教师不符');
            }
            
            $teacherName = M('ucenter_member')->where(array('id'=>$teacherId))->getField('username');
            $comment = M('teacher_comment');
            $res = $comment->where(array('tc_tagId'=>$tagId, 'tc_classTableId'=>$classTableId, 'tc_teacherId'=>$teacherId))->find();
            if($res){
                $result = $comment->where(array('tc_id'=>$res['tc_id']))->setInc('tc_count', 1);
            }else {
                $data = array(
                    'tc_tagId' => $tagId,
                    'tc_count' => 1,
                    'tc_classTableId' => $classTableId,
                    'tc_className' => $className,
                    'tc_teacherId' => $teacherId,
                    'tc_teacherName' =>$teacherName,
                    'tc_content' => $content
                );
                $result = $comment->add($data);
            }
            if($result){
                $this->success('评论成功');
            }else {
                $this->error('评论失败');
            }
        }else {
            $tags = M('comment_tag')->field(array('ct_id as id', 'ct_name as name'))->select();
            $this->tags = json_encode($tags);
            $this->display(__FUNCTION__);
        }
    }
    
    /*
     * 删除评价
     * @param $id 评价id
     * */
    public function del(){
        if(IS_AJAX){
            $ids = I('id');
            $ids = !is_array($ids) ? $ids : implode(',', $ids);
            empty($ids) && $this->ajaxReturn(array('status'=>0, 'msg'=>'参数非法'), 'json');
            
            //$res = M('Comment')->where(array('tc_id'=>$id))->delete();
            $res = D('Common/TeacherComment')->delInfoCache($ids);
            
            if($res) {
                action_log('Delete_Comment', 'Comment', $ids, UID);
                $data = array('status'=>1, 'msg'=>'删除成功');
            }else {
                $data = array('status'=>0, 'msg'=>'删除失败');
            }
            $this->ajaxReturn($data, 'json');
        }else {
            $this->error('非法操作');
        }
        
    }
    
}
