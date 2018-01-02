<?php
/*
 * 公用数据
 */
namespace Home\Controller;

use Think\Controller;
use Member\Api\MemberApi as MemberApi;
use OSS\Core\OssException;
use Attachment\Api\AttachmentApi as AttachmentApi;


defined('in_lqweb') or exit('Access Invalid!');

class GlobalController extends PublicController
{
    /*
     * ajax获取二级课程分类
     */
    public function getLessonCat()
    {
        $cat_id = I("get.cat_id", "0");
        foreach (F('lesson_cat', '', COMMON_ARRAY) as $key => $val) {
            if ($val['zn_fid'] == $cat_id) $cat_arr[] = array("id" => $val['id'], "title" => $val['zc_caption']);
        };
        $this->ajaxReturn($cat_arr);
    }

    /*
     * 获取广告
     */
    /////广告
    public function getAd()
    {
        $data = D("AdPosition")->getAdPositionById($this->lqgetid)["list"];
        if ($data) {
            $this->ajaxReturn(array('status' => 1, 'data' => $data));
        } else {
            $this->ajaxReturn(array('status' => 0, 'data' => ''));
        }

    }

    /*
     * 阿里云oss上传
     */
    public function oss_upload()
    {
        $data = array(
            "status" => 0,
            "msg" => '上传失败,请重试...'
        );
        $localPath = RUNTIME_PATH . 'Oss/';
        $upload = new \Think\Upload();
        $upload->maxSize = 100 * 1024 * 1024;// 设置附件上传大小 100M
//        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = $localPath; // 设置附件上传根目录
        $upload->saveName = array('uniqid', '');

        $info = $upload->upload();
        if (!$info) {
            $this->ajaxReturn($data);
        } else {// 上传成功
            vendor('AliOss.autoload');
            $accessKeyId = C('ALI_API')['AccessKeyId'];//去阿里云后台获取秘钥
            $accessKeySecret = C('ALI_API')['AccessKeySecret'];//去阿里云后台获取秘钥
            $endpoint = C('ALI_API')['endpoint'];//你的阿里云OSS地址
            $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);

            $bucket = C('ALI_API')['bucket'];//oss中的文件上传空间
            $object = date('Y-m-d') . '/' . $info['imgfile']['savename'];//想要保存文件的名称
            $file = $localPath . $info['imgfile']['savepath'] . $info['imgfile']['savename'];//文件路径，必须是本地的。
            try {
                $reData = $ossClient->uploadFile($bucket, $object, $file);
                ///$reData['info']['url']
                $data = array(
                    "status" => 1,
                    "msg" => '上传成功...'
                );

                unlink($file);
                $this->ajaxReturn($data);

            } catch (OssException $e) {
                $data['msg'] = $e->getMessage();
                $this->ajaxReturn($data);
                return;
            }
        }
    }

    public function getObjectToLocalFile()
    {
        $fileurl = 'http://live-upload.oss-cn-shanghai.aliyuncs.com/2017-07-27/5979afd073f9c.pptx';
        download($fileurl);
        /*$fileurl  = 'http://live-upload.oss-cn-shanghai.aliyuncs.com/2017-07-27/5979afd073f9c.pptx';
        vendor('AliOss.autoload');
        $accessKeyId = C('ALI_API')['AccessKeyId'];//去阿里云后台获取秘钥
        $accessKeySecret = C('ALI_API')['AccessKeySecret'];//去阿里云后台获取秘钥
        $endpoint =  C('ALI_API')['endpoint'];//你的阿里云OSS地址
        $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);

        $bucket = C('ALI_API')['bucket'];//oss中的文件上传空间

        $object = $fileurl;
        $localfile = end(explode('/',$object));
        $options = array(
            'fileDownload' => $localfile,
        );
        try{
            $ossClient->getObject($bucket, $object, $options);
        } catch(OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK, please check localfile: 'upload-test-object-name.txt'" . "\n");*/
    }

    ////删除oss
    function deleteObject($ossClient, $bucket)
    {
        $object = "oss-php-sdk-test/upload-test-object-name.txt";
        try {
            $ossClient->deleteObject($bucket, $object);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK" . "\n");
    }

    public function getGrowSchool()
    {
        foreach (F('school_cat', '', COMMON_ARRAY) as $k => $v) {
            $school[$k] = $v['zc_caption'];
        }

        $data = array(
            "status" => 1,
            "data" => $school
        );
        $this->ajaxReturn($data);
    }
}
?>