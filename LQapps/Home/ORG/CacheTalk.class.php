<?php
namespace Home\ORG;

class CacheTalk
{
    protected $cache;
    protected $cache_id;
    private $talkArr = array();// 记录聊天缓存

    /////缓存初始化
    public function __construct($cache_id)
    {
        $this->cache_id = $cache_id;
        $this->cache = S(array('prefix' => "talk_live_" . $this->cache_id . C("S_PREFIX"), 'temp' => C("COMMON_TALK"),'expire'=>3600*24));
        $this->talkArr = $this->get_talk();
    }

    /** 聊天缓存
     * @param $uid
     * @param $img
     * @param $name
     * @param $value
     * @return mixed
     */
    public function add_talk($uid,$img,$name ,$value)
    {
        if ($this->talkArr) {
            array_push($this->talkArr, array("uid" => $uid, "img" => $img, "name" => $name, "value" => $value));
        } else {
            $this->talkArr[0] = array("uid" => $uid, "img" => $img, "name" => $name, "value" => $value);
        }

        $this->cache->name = $this->talkArr;

        return $this->get_talk();
    }

    public function get_talk()
    {
        return $this->cache->name;
    }
}