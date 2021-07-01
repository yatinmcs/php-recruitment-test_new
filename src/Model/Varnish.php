<?php

namespace Snowdog\DevTest\Model;

class Varnish
{

    public $varnish_id;
    public $ip_address;
    public $user_id;

    public function __construct()
    {
        $this->varnish_id = intval($this->varnish_id);
        $this->user_id = intval($this->user_id);
    }

    /**
     * @return int
     */
    public function getVarnishId()
    {
        return $this->varnish_id;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }
}