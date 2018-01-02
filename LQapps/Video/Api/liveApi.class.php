<?php
/**
 * Date: 2017-06-23
 * Time: 14:51
 * 视频直播API
 */

namespace Video\Api;

use Video\Api\Api;

class liveApi extends Api
{
    protected $aliLive;
    protected $domainName;

    public function __construct()
    {
        parent::__construct();
        $this->aliLive = new Api();
        $this->domainName = C("ALI_API")['vhost'];
    }


    /**
     * 生成推流地址
     * @param $streamName 用户专有名
     * @param $vhost 加速域名
     * @param $time 有效时间单位秒
     */
    public function getPushSteam($streamName, $expireTime = '')
    {
        $time = $expireTime == "" ? time() + C('ALI_API')['expireTime'] : $expireTime;
        $videohost = $this->videoHost;
        $vhost = $this->vhost;
        $appName = $this->appName;
        $privateKey = $this->privateKey;
        if ($privateKey) {
            $auth_key = md5('/' . $appName . '/' . $streamName . '-' . $time . '-0-0-' . $privateKey);
            $url = $videohost . '/' . $appName . '/' . $streamName . '?vhost=' . $vhost . '&auth_key=' . $time . '-0-0-' . $auth_key;
        } else {
            $url = $videohost . '/' . $appName . '/' . $streamName . '?vhost=' . $vhost;
        }
        return $url;
    }

    /**
     * 生成拉流地址
     * @param $streamName 用户专有名
     * @param $vhost 加速域名
     * @param $type 视频格式 支持rtmp、flv、m3u8三种格式
     */
    public function getPullSteam($streamName, $expireTime = '', $type = 'rtmp')
    {
        $time = $expireTime == "" ? time() + C('ALI_API')['expireTime'] : $expireTime;
        $vhost = $this->vhost;
        $appName = $this->appName;
        $privateKey = $this->privateKey;
        $url = '';
        switch ($type) {
            case 'rtmp':
                $host = 'rtmp://' . $vhost;
                $url = '/' . $appName . '/' . $streamName;
                break;
            case 'flv':
                $host = 'http://' . $vhost;
                $url = '/' . $appName . '/' . $streamName . '.flv';
                break;
            case 'm3u8':
                $host = 'http://' . $vhost;
                $url = '/' . $appName . '/' . $streamName . '.m3u8';
                break;
        }
        if ($privateKey) {
            $auth_key = md5($url . '-' . $time . '-0-0-' . $privateKey);
            $url = $host . $url . '?auth_key=' . $time . '-0-0-' . $auth_key;
        } else {
            $url = $host . $url;
        }
        return $url;
    }

    /**
     * 查询应用直播截图信息
     * @param $appName         应用名
     * @param $streamName      推流名
     */
    public function DescribeLiveStreamSnapshotInfo($streamName, $startTime, $endTime)
    {
        $apiParams = array(
            'Action' => 'DescribeLiveStreamSnapshotInfo',
            'DomainName' => $this->domainName,
            'AppName' => $this->appName,
            'StreamName' => $streamName,
            'StartTime' => $startTime,
            'EndTime' => $endTime,
        );
        return $this->aliLive->aliApi($apiParams);
    }


    /**
     * 查询应用直播在线人数
     * @param $appName     应用名
     * @param $streamName  推流名
     */
    public function DescribeLiveStreamOnlineUserNum($streamName, $startTime = '', $endTime = '')
    {
        if ($startTime && $endTime) {
            $apiParams = array(
                'Action' => 'DescribeLiveStreamOnlineUserNum',
                'DomainName' => $this->domainName,
                'AppName' => $this->appName,
                'StreamName' => $streamName,
                'StartTime' => $startTime,
                'EndTime' => $endTime
            );
        } else {
            $apiParams = array(
                'Action' => 'DescribeLiveStreamOnlineUserNum',
                'DomainName' => $this->domainName,
                'AppName' => $this->appName,
                'StreamName' => $streamName,
            );
        }
        return $this->aliLive->aliApi($apiParams);
    }


    /**
     * 查看某个域名下所有流的信息
     * @param $appName         应用名
     * @param $streamName      推流名
     */
    public function DescribeLiveStreamsPublishList($streamName, $startTime, $endTime)
    {
        $apiParams = array(
            'Action' => 'DescribeLiveStreamsPublishList',
            'AppName' => $this->appName,
            'StreamName' => $streamName,
            'StartTime' => $startTime,
            'EndTime' => $endTime,
        );
        return $this->aliLive->aliApi($apiParams);
    }

    /**
     * 查看指定域名下（或者指定域名下某个应用）的所有正在推的流的信息
     * @param $appName          应用名
     * @return bool|int|mixed
     */
    public function describeLiveStreamsOnlineList()
    {
        $apiParams = array(
            'Action' => 'DescribeLiveStreamsOnlineList',
            'AppName' => $this->appName,
            'DomainName' => $this->domainName
        );
         $rsObj = object2array(json_decode($this->aliLive->aliApi($apiParams)));
//        $str = '    {
//        "RequestId": "0D70427D-91E4-4349-AAD3-5511A5BB823B",
//        "OnlineInfo": {
//            "LiveStreamOnlineInfo": [
//                {
//                    "AppName": "xchen",
//                    "StreamName": "4_37_297",
//                    "PublishTime": "2015-12-02T06:58:04Z",
//                    "PublishUrl": "rtmp://video-center.alivecdn.com/zier21live/4_31_255?vhost=live.zier21.com&auth_key=1502478180-0-0-cb20db0410825dad6b976eb27a43db5c",
//                    "DomainName": "test101.aliyunlive.com"
//                },
//                {
//                    "AppName": "xchen2",
//                    "StreamName": "5_32_255",
//                    "PublishTime": "2015-12-02T06:58:04Z",
//                    "PublishUrl": "rtmp://video-center.alivecdn.com/zier21live/5_32_255?vhost=live.zier21.com&auth_key=1502478180-0-0-cb20db0410825dad6b976eb27a43db5c",
//                    "DomainName": "test101.aliyunlive.com"
//                }
//            ]
//        }
//    }';
//        $rsObj = object2array(json_decode($str));
        $reArr = $rsObj['OnlineInfo']['LiveStreamOnlineInfo'];
        foreach ($reArr as $key => $val) {
            $streamName_s = explode("_", $val['StreamName']);
            $reArr[$key]['streamName_s'] = $streamName_s[0] . "_" . $streamName_s[1];
        }
        return $reArr;
    }

    /**
     * 查询推流黑名单列表
     * @return bool|int|mixed
     */
    public function describeLiveStreamsBlockList()
    {
        $apiParams = array(
            'Action' => 'DescribeLiveStreamsBlockList',
            'DomainName' => $this->domainName,
        );
        return $this->aliLive->aliApi($apiParams);
    }


    /**
     * 禁止推流接口
     * @param $appName          应用名称
     * @param $streamName       流名称
     * @param $liveStareamName  用于指定主播推流还是客户端拉流, 目前支持”publisher” (主播推送)
     * @param $resumeTime       恢复流的时间 UTC时间 格式：2015-12-01T17:37:00Z
     * @return bool|int|mixed
     */
    public function forbid($streamName, $resumeTime, $liveStreamType = 'publisher')
    {
        $apiParams = array(
            'Action' => 'ForbidLiveStream',
            'DomainName' => $this->domainName,
            'AppName' => $this->appName,
            'StreamName' => $streamName,
            'LiveStreamType' => $liveStreamType,
            'ResumeTime' => $resumeTime
        );
        return $this->aliLive->aliApi($apiParams);
    }

    /**
     * 恢复直播流推送
     * @param $streamName              流名称
     * @param string $appName 应用名称
     * @param string $liveStreamType 用于指定主播推流还是客户端拉流, 目前支持”publisher” (主播推送)
     * @param string $domainName 您的加速域名
     */
    public function resumeLive($streamName, $liveStreamType = 'publisher')
    {
        $apiParams = array(
            'Action' => 'ResumeLiveStream',
            'DomainName' => $this->domainName,
            'AppName' => $this->appName,
            'StreamName' => $streamName,
            'LiveStreamType' => $liveStreamType,
        );
        return $this->aliLive->aliApi($apiParams);
    }


}