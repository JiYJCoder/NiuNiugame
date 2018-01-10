<?php
namespace LQLibs\Util;

/**
 * 推送事件
 * 典型调用方式：
 * $push = new PushEvent();
 * $push->setUser($user_id)->setContent($string)->push();
 *
 * Class PushEvent
 * @package app\lib\event
 */
class PushEvent
{
    /**
     * @var string 目标用户id
     */
    protected $to_user = '';

    /**
     * @var string 推送服务地址
     */
    protected $push_api_url = 'http://127.0.0.1:2121/';

    /**
     * @var string 推送内容
     */
    protected $content = '';

    /**
     * 设置推送用户，若参数留空则推送到所有在线用户
     *
     * @param string $user
     * @return $this
     */
    public function setUser($user = '')
    {
        $this->to_user = $user ?: '';
        return $this;
    }

    /**
     * 设置推送内容
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content = '')
    {
        $this->content = $content;
        return $this;
    }

    /**
     * 推送
     */
    public function push()
    {
        /*$header = array('Expect:');

        $data = array(
            'type' => 'publish',
            'content' => $this->content,
            'to' => $this->to_user,
        );
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_URL, $this->push_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        $res = curl_exec($ch);
        curl_close($ch);
        dump($res);*/



        $data = array(
            'type' => 'publish',
            'content' => $this->content,
            'to' => $this->to_user,
        );
        $header = array('Expect:');
        try{
            $ch = curl_init();
            if (substr($this->push_api_url, 0, 5) == 'https') {
                // 跳过证书检查
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                // 从证书中检查SSL加密算法是否存在
                // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $this->push_api_url);// 设置请求的url
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);// 设置请求的HTTP Header
            // 设置允许查看请求头信息
            // curl_setopt($ch,CURLINFO_HEADER_OUT,true);
            curl_setopt($ch, CURLOPT_POST, true);// 请求方式是POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));// 设置发送的data
            $response = curl_exec($ch);
            // 查看请求头信息
            // dump(curl_getinfo($ch,CURLINFO_HEADER_OUT));
            if ($error = curl_error($ch)) {
                // 如果发生错误返回错误信息
                curl_close($ch);
                $ret=['status'=>false,'msg'=>$error];
                var_dump( $ret );
            } else {
                // 如果发生正确则返回response
                curl_close($ch);
                $ret=['status'=>true,'msg'=>$response];
                var_dump( $ret );
            }
        }catch (\Exception $exception){
            $ret=['status'=>false,'msg'=>$exception->getMessage()];
            var_dump( $ret );
        }
    }
}