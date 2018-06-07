<?php

namespace Admin\Controller;
use Think\Page;

/**
 * 后台课件管理控制器
 */
class CourseResourceController extends AdminController {

    /**
     * 课程管理首页
     */
    public function index(){
        $this->meta_title = '课件资源管理首页';
        $fields = array('cr_id', 'cr_name', 'cr_description', 'cr_courseName', 'cr_userName', 'cr_userId','cr_type', 'cr_isPublic', 'cr_readCount', 'cr_updateTime', 'cr_createTime','br_name', 'sd_name');
        
        $where['cr_branchId'] = array('in', implode(',', $this->branchIds));
        $where['cr_userId'] = array('neq', UID);
        $where['cr_isPublic'] = array('eq', 1);
        $map['_complex'] = $where;
        $map['cr_userId'] = UID;
        $map['_logic'] = 'or';
        
        if(isset($_GET['pid'])){
            $this->search($map, $fields);
        }else {
            $model = D('Common/CourseResource');
            $count = $model->where($map)->count();
            $page = new Page($count, C("SHOW_PAGE_SIZE"));
            $limit = $page->firstRow . ',' . $page->listRows;
            $result = $model->getCourseResourceDataCache($map, $limit, $fields);
            
            $this->result = $result;
            $this->show = $page->show();
        }
        
        $this->uid = UID;
        $this->typeArr = array('文件文档', '视频', '考试题目', '网页文章');
        $this->display();
    }
    
    //添加课件资源
    public function add(){
        if(IS_POST){
            $type = session('addCourseResourceType');//资源类型
            $course = D('Common/CourseResource');
            if($type == 0){//文件
                $crd_url = I('post.crd_url');
                $fileList = $course->fileUrlHandle($crd_url);
                !$fileList && $this->error('请选择文件');
                
                $info['crd_url'] = serialize($fileList);
                $fieldName = 'crd_resourceId';
                $model = 'course_resource_file';
            }else if($type == 1){//视频地址
                $video = I('post.crd_url');
                $videoList = $course->fileUrlHandle($video, 'url');
                is_array($videoList) && empty($videoList) && $this->error('请输入视频地址');
                $videoList == false && $this->error('视频地址不合法');
                
                $info['crd_url'] = serialize($videoList);
                $fieldName = 'crd_resourceId';
                $model = 'course_resource_file';
            }else if($type == 3){//网页文章
                $content = I('crp_content', '');
                empty($content) && $this->error('请填写内容');
                
                $info['crp_content'] = $content;
                $fieldName = 'crp_resourceId';
                $model = 'course_resource_page';
            }else {
                $this->error('非法操作');
            }
            
            $courseId = I('cr_courseId', 0, 'intval');
            $courseBranchId = M('course')->getFieldByCoId($courseId, 'co_branchId');
            if(is_null($courseBranchId) || !in_array($courseBranchId, $this->branchIds)){
                $this->error('您没有选择该课程的权限');
            }
            
            $res = $course->addInfoCacheClean($info, $fieldName, $model, $type);
            !$res && $this->error($course->getError()?$course->getError() : '新增失败');
            
            session('addCourseResourceType', null);
            action_log('add_Course_Resource', 'Course_Resource', $res, UID);
            $this->success('新增成功', U('CourseResource/index'));
        } else {
            $type = I('type', 0, 'intval');
            session('addCourseResourceType', $type);//保存添加资源类型
            
            $this->meta_title = '新增课件资源';
            $this->uploadData = array('title'=>'添加文件', 'action'=>'add', 'name'=>'crd_url', 'url'=>U('File/upload',array('session_id'=>session_id(), 'act'=>'all')), 'type'=>'file', 'ext'=>C('DOWNLOAD_UPLOAD')['exts']);
            $this->type = $type;
            $this->display();
        }
    }
    
    /*
     * 编辑课件资源
     * */
    public function edit(){
        if(IS_POST){
            $type = session('resource_type');
            $course = D('Common/CourseResource');
            if($type == 0){//文件
                $crd_url = I('post.crd_url');
                empty($crd_url) && $this->error('请选择文件');
                
                $info['crd_url'] = serialize($course->fileUrlHandle($crd_url));
                $model = 'course_resource_file';
                $fieldName = 'crd_resourceId';
            }else if($type == 1){//视频地址
                $video = I('post.crd_url');
                $videoList = $course->fileUrlHandle($video, 'url');
                is_array($videoList) && empty($videoList) && $this->error('请输入视频地址');
                $videoList == false && $this->error('视频地址不合法');
                
                $info['crd_url'] = serialize($videoList);
                $fieldName = 'crd_resourceId';
                $model = 'course_resource_file';
            }else if($type == 3){//网页文章
                $content = I('crp_content', '');
                empty($content) && $this->error('网页文章内容不能为空');
                
                $info['crp_content'] = trim($content);
                $model = 'course_resource_page';
                $fieldName = 'crp_resourceId';
            }else {
                $this->error('非法操作');
            }
            
            $courseId = I('cr_courseId', 0, 'intval');
            $courseId == 0 && $this->error('请选择课程');
            $branchId = M('course')->getFieldByCoId($courseId, 'co_branchId');
            if(!in_array($branchId, $this->branchIds)){
                $this->error('您没有选择该课程的权限！');
            }
            
            $res = $course->editInfoCacheClean($info, $fieldName, $model);
            !$res && $this->error($course->getError()?$course->getError() : '编辑失败');
            
            session('resource_type', null);
            $this->success('编辑成功', U('CourseResource/index'));
        }else {
            $id = I('id', 0, 'intval');
            empty($id) && $this->error('非法参数');
            $type = M('course_resource')->getFieldByCrId($id, 'cr_type');
            
            $fields = array('cr_id', 'cr_name', 'cr_description', 'cr_courseId', 'cr_isPublic', 'cr_branchId', 'co_name');
            $where = array('cr_id'=>$id);
            if($type == 0 || $type == 1){
                $fields[] = 'crd_url';
                $info = D('Common/CourseResource')->getCourseResourceFileCache($where, $fields);
                $info['crd_url'] = unserialize($info['crd_url']);
                $this->uploadData = array('title'=>'添加文件', 'action'=>'edit', 'name'=>'crd_url', 'url'=>U('File/upload',array('session_id'=>session_id(), 'act'=>'all')), 'type'=>'file', 'ext'=>C('DOWNLOAD_UPLOAD')['exts']);
            }else if($type == 3){
                $fields[] = 'crp_content';
                $info = D('Common/CourseResource')->getCourseResourcePageCache($where, $fields);
            }
            
            session('resource_type', $type);
            
            $this->meta_title = '编辑课件资源';
            $this->courseName = $info['co_name'];
            $this->info = $info;
            $this->type = $type;
            $this->display();
        }
    }
    
    public function view(){
        $id = I('id', 0, 'intval');
        empty($id) && $this->error('非法参数');
        $type = M('course_resource')->getFieldByCrId($id, 'cr_type');
        
        $fields = array('cr_id', 'cr_name', 'cr_description', 'cr_courseId', 'cr_isPublic', 'cr_branchId', 'co_name');
        $where = array('cr_id'=>$id);
        if($type == 0 || $type == 1){
            $fields[] = 'crd_url';
            $info = D('Common/CourseResource')->getCourseResourceFileCache($where, $fields);
            $info['crdUrl'] = unserialize($info['crd_url']);
            $this->uploadData = array('title'=>'添加文件', 'action'=>'edit', 'name'=>'crd_url', 'url'=>U('File/upload',array('session_id'=>session_id(), 'act'=>'all')), 'type'=>'file', 'ext'=>C('DOWNLOAD_UPLOAD')['exts']);
        }else if($type == 3){
            $fields[] = 'crp_content';
            $info = D('Common/CourseResource')->getCourseResourcePageCache($where, $fields);
        }
        
        session('resource_type', $type);
        
        $this->meta_title = '编辑课程';
        $this->courseName = $info['co_name'];
        $this->info = $info;
        $this->type = $type;
        $this->display();
    }
    
    /* 删除课资源
     * @param $id 课程id
     * */
    public function del(){
        if(IS_AJAX){
            $id = I('id');
            empty($id) && $this->ajaxReturn(array('status'=>0, 'msg'=>'参数不能为空'), 'json');
            $ids = !is_array($id) ? array($id) : $id;
            
            $where['cr_id'] = array('in', implode(',', $ids));
            $branchIds = M('course_resource')->where($where)->getField('cr_branchId', true);
            $branchIds = array_unique($branchIds);
            
            $diff = array_diff($branchIds, $this->branchIds);
            !empty($diff) && $this->ajaxReturn(array('status'=>0, 'msg'=>'您没有权限'), 'json');
            
            $res = D('CourseResource')->delInfoCacheClean($ids);
            !$res && $this->ajaxReturn(array('status'=>0, 'msg'=>'删除失败'), 'json');
            
            action_log('Delete_Course_Resource', 'Course_Resource', implode(',', $ids), UID);//记录行为
            $this->ajaxReturn(array('status'=>1, 'msg'=>'删除成功'), 'json');
        }else {
            $this->error('非法操作');
        }
    }
    
    /*
     * 共享课件资源
     * @param $id 资源id
     * @param $act 是否共享    0：取消共享   1：共享
     * */
    public function share(){
        if(IS_AJAX){
            $id = I('id', 0, 'intval');
            $act = I('act', 0, 'intval');
            $id == 0 && $this->ajaxReturn($data = array('status'=>0, 'msg'=>'非法参数'), 'json');
            $action = $act == 1 ? '共享' : '取消共享';
            $actionName = $act == 1 ? 'SHARE' : 'CANCEL';
            $res = D('Common/CourseResource')->shareHandleCacheClean($id, $act);
            if($res){
                //记录行为
                action_log($actionName . '_COURSE_RESOURCE', 'Course_Resource', $id, UID);
                $data = array('status'=>1, 'msg'=>$action.'成功');
            }else {
                $data = array('status'=>0, 'msg'=>$action.'失败');
            }
            $this->ajaxReturn($data, 'json');
        }else {
            $this->error('非法操作');
        }
        
    }
    
    private function search($where, $fields){
        /* 查询条件初始化 */
        $map = array();
        $map['_complex'] = $where;
        if(isset($_GET['title'])){
            $title = (string)I('title');
            $condition['cr_name']  = array('like', '%'.$title.'%');
            $condition['cr_description']  = array('like', '%'.$title.'%');
            $condition['cr_courseName']  = array('like', '%'.$title.'%');
            $condition['_logic']  = 'or';
            $map['_complex'] = $condition;
        }
        
        if ( isset($_GET['time-start']) ) {
            $map['cr_createTime'][] = array('egt', I('time-start'));
        }
        if ( isset($_GET['time-end']) ) {
            $map['cr_createTime'][] = array('elt', I('time-end'));
        }
        if ( isset($_GET['nickname']) ) {
            $map['cr_userId'] = M('Member')->where(array('nickname'=>I('nickname')))->getField('uid');
        }
        
        $join = array(
            "LEFT JOIN __BRANCH__ ON __COURSE_RESOURCE__.cr_branchId=__BRANCH__.br_id",
            "LEFT JOIN __COURSE__ ON cr_courseId=co_id",
            "LEFT JOIN __STUDY_DIRECTION__ on co_studyDirectionId=sd_id",
        );
        $list = $this->lists('course_resource',$map,'cr_createTime desc', '', $fields, $join);
        
        $this->result = $list;
    }
    
}
