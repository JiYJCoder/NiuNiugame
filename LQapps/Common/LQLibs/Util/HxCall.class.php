<?php
namespace LQLibs\Util;

class Hxcall
{
    private $app_key = '';
    private $client_id = '';
    private $client_secret = '';
    private $url = "";
    private $token_path = "";

    /*
    * 获取APP管理员Token
    */
    public function __construct($config) {
        if($config){
            $this->app_key=$config["app_key"];
            $this->client_id=$config["client_id"];
            $this->client_secret=$config["client_secret"];
            $this->url=$config["url"];
            $this->token_path=$config["token_path"];
        }

        if(file_exists($this->token_path) && time()-filemtime($this->token_path)<=86400)
        {
            $this->token = file_get_contents($this->token_path);
        }
        else
        {
            $url = $this->url . "/token";
            $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
            );
            $rs = json_decode($this->curl($url, $data), true);
            $this->token = $rs['access_token'];
            file_put_contents($this->token_path, $rs['access_token']);
        }
    }
    /*
    * 注册IM用户(授权注册)
    */
    public function register($username, $password, $nickname)
    {
        $url = $this->url . "/users";
        $data = array(
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname
        );
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, $data, $header, "POST");
    }

    /*
    *
    * curl
    */
    private function curl($url, $data, $header = false, $method = "POST")
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $ret = curl_exec($ch);
        return $ret;
    }
}
?>