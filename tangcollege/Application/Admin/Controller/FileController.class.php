<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Admin\Controller;
/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */
class FileController extends AdminController {

    /* 文件上传 */
    public function upload(){
		$return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
		/* 调用文件上传组件上传文件 */
		$File = D('File');
		$file_driver = C('DOWNLOAD_UPLOAD_DRIVER');
		$info = $File->upload(
			$_FILES,
			C('DOWNLOAD_UPLOAD'),
			C('DOWNLOAD_UPLOAD_DRIVER'),
			C("UPLOAD_{$file_driver}_CONFIG")
		);
// dump($info);die;
        /* 记录附件信息 */
        if($info){
            if(I('request.act') == 'all'){
                $return['data'] = $info['download'];
                $return['info'] = $info['download']['name'];
            }else {
                $return['data'] = think_encrypt(json_encode($info['download']));
                $return['info'] = $info['download']['name'];
            }
        } else {
            $return['status'] = 0;
            $return['info']   = $File->getError();
        }

        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }

    /* 下载文件 */
    public function download($id = null){
        if(empty($id) || !is_numeric($id)){
            $this->error('参数错误！');
        }

        $logic = D('Download', 'Logic');
        if(!$logic->download($id)){
            $this->error($logic->getError());
        }

    }

    /**
     * 上传图片
     * @author huajie <banhuajie@163.com>
     */
    public function uploadPicture(){
        //TODO: 用户登录检测

        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');

        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        ); //TODO:上传到远程服务器

        /* 记录图片信息 */
        if($info){
            $return['status'] = 1;
            $return = array_merge($info['download'], $return);
        } else {
            $return['status'] = 0;
            $return['info']   = $Picture->getError();
        }

        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }
	
	public function uploadFile(){
		$info = upload(1);
		/* 返回JSON数据 */
        $this->ajaxReturn($info);
	}
    
    /*
     * 删除图片及tang_file/tang_picture表中的记录
     * */
    public function delFile(){
        if(IS_AJAX){
            $id = I('id', 0, 'intval');
            $type = I('type', '');
            $md5 = I('md5', '');
            if($type == 'image'){
                $model = M('Picture')->where(array('id'=>$id, 'md5'=>$md5));
                $path = $model->getField(array('Path'));
                if($path){//暂时不做文件是否存在
                    $res = M('Picture')->where(array('id'=>$id, 'md5'=>$md5))->delete();
                    $result = unlink($path);
                    if($res && $result){
                        $this->ajaxReturn(array('state'=>1, 'msg'=>'success'), 'json');
                    }else {
                        $this->ajaxReturn(array('state'=>0, 'msg'=>'faile'), 'json');
                    }
                }else {
                    $this->ajaxReturn(array('state'=>0, 'msg'=>'not exists'), 'json');
                }
            }else if($type == 'file') {
                $model = M('File')->where(array('id'=>$id, 'md5'=>$md5));
                $data = $model->field(array('savename', 'savepath'))->find();
                if($data){//删除远程文件暂时不做
                    $path = './Uploads/Download/' . $data['savepath'] . $data['savename'];
                    $res = M('File')->where(array('id'=>$id, 'md5'=>$md5))->delete();
                    $result = unlink($path);
                    if($res && $result){
                        $this->ajaxReturn(array('state'=>1, 'msg'=>'success'), 'json');
                    }else {
                        $this->ajaxReturn(array('state'=>0, 'msg'=>'faile'), 'json');
                    }
                }else {
                    $this->ajaxReturn(array('state'=>0, 'msg'=>'not exists'), 'json');
                }
            }
        }else {
            $this->error('非法操作');
        }
    }
}
