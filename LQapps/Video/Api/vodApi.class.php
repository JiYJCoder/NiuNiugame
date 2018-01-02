<?php
/*
 * 视频点播openApi
 */
namespace Video\Api;
use Video\Api\Api;

class vodApi extends Api
{
    protected $aliLive;
    protected $domainName;
    private $apiUrl;

    public function __construct()
    {
        parent::__construct();
        $this->apiUrl = C('ALI_API')['apiVodDomain'];
        $this->aliLive = new Api();
        $this->domainName = C("ALI_API")['vhost'];
    }
    /**
     * 获取上传id和凭证
     * @param Action  接口名
     * @param string Title    视频标题
     * @param string FileName 视频文件名
     * @param string FileSize 视频文件大小
     */
    public function CreateUploadVideo($title, $fileName, $fileSize)
    {
        $apiParams = array(
            'Action' => 'CreateUploadVideo',
            'Title' => $title,
            'FileName' => $fileName,
            'FileSize' => $fileSize,
        );
        return $this->aliLive->aliApi($apiParams,$this->apiUrl);
    }

    /**
     * 刷新上传凭证
     * @param Action  接口名
     * @param string vodID    视频id
     */
    public function RefreshUploadVideo($vodID)
    {
        $apiParams = array(
            'Action' => 'RefreshUploadVideo',
            'VideoId' => $vodID,
        );
        return $this->aliLive->aliApi($apiParams,$this->apiUrl);
    }
    /**
     * 通过视频id获取视频
     * @param Action  接口名
     * @param string VideoId    视频id
     * @param string Formats 视频流格式，多个用逗号分隔，支持格式mp4,m3u8,mp3，默认获取所有格式的流
     * @param string AuthTimeout 播放鉴权过期时间，默认为1800秒
     */
    public function GetPlayInfo($vodID, $formats='', $authTimeout ='')
    {
        $apiParams = array(
            'Action' => 'GetPlayInfo',
            'VideoId' => $vodID,
            'Formats' => $formats,
            'AuthTimeout' => $authTimeout,
        );
        return $this->aliLive->aliApi($apiParams,$this->apiUrl);
    }

    /**
     * 获取视频播放凭证
     * @param Action  接口名
     * @param string vodID    视频id
     */
    public function GetVideoPlayAuth($vodID)
    {
        $apiParams = array(
            'Action' => 'GetVideoPlayAuth',
            'VideoId' => $vodID,
        );
        return $this->aliLive->aliApi($apiParams,$this->apiUrl);
    }

    /**
     * 删除视频
     * @param Action  接口名
     * @param string vodID    视频id
     */
    public function DeleteVideo($vodID)
    {
        $apiParams = array(
            'Action' => 'DeleteVideo',
            'VideoIds' => $vodID,
        );
        return $this->aliLive->aliApi($apiParams,$this->apiUrl);
    }
}

?>