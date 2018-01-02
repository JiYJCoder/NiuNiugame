<?php
/*
 * oss附件管理
 */
namespace Video\Api;

class ossApi
{
    ////oss对象
    private $ossClient, $bucket;

    public function __construct()
    {
        vendor('ALiOss.autoload');
        $accessKeyId = C('ALI_API')['AccessKeyId'];//去阿里云后台获取秘钥
        $accessKeySecret = C('ALI_API')['AccessKeySecret'];//去阿里云后台获取秘钥
        $endpoint = C('ALI_API')['endpoint'];//你的阿里云OSS地址
        $this->ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);

        $this->bucket = C('ALI_API')['bucket'];//oss中的文件上传空间
    }

    /**
     * 文件上传
     * @param Action  接口名
     * @param string $object 上传到oss文件名
     * @param string $file 本地文件
     */
    public function ossUpload($object, $file)
    {
        try {
            $reData = $this->ossClient->uploadFile($this->bucket, $object, $file);

            $data = array(
                "status" => 1,
                "data" => $reData['info']['url']
            );

            unlink($file);
            return $data;
        } catch (OssException $e) {
            $data = array(
                "status" => 0,
                "data" => $e->getMessage()
            );
            return $data;
        }
    }

    ////判断文件是否存在
    function doesObjectExist($object)
    {
        try {
            $exist = $this->ossClient->doesObjectExist($this->bucket, $object);
        } catch (OssException $e) {
            $data = array(
                "status" => $e->getMessage()
            );
            return $data;
        }
        $data = array(
            "status" => intval($exist)
        );
        return $data;
    }

    ////删除oss
    function deleteObject($object)
    {
        try {
            $this->ossClient->deleteObject($this->bucket, $object);
            $data = array(
                "status" => 1,
                "msg" => '删除成功'
            );
        } catch (OssException $e) {
            $data = array(
                "status" => 0,
                "msg" => $e->getMessage()
            );
            return $data;
        }
        return $data;
    }

}

?>