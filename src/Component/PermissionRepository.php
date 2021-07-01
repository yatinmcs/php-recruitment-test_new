<?php

namespace Snowdog\DevTest\Component;

use SimpleAcl\Acl;
use SimpleAcl\Resource;
use SimpleAcl\Role;

class PermissionRepository
{
    const ACCESS_LEVEL_GUEST = 'guest';
    const ACCESS_LEVEL_USER  = 'user';

    const RESOURCE_APP_FRONT = 'frontend';
    const RESOURCE_APP_BACK  = 'backend';

    private static $instance = null;
    private static $acl;

    private static $roles = [];
    private static $resources = [];
    
    public static $redirects = [];

    /**
     * @return PermissionRepository
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public static function getUserState() : string
    {
        $login = isset($_SESSION['login']);
        if ($login && !empty($login)) {
            return self::ACCESS_LEVEL_USER;
        } else {
            return self::ACCESS_LEVEL_GUEST;
        }
    }

    /**
     * Create application Access Control Layer instance
     *
     * @return Acl
     */
    public static function createAcl() : Acl
    {
        self::setupAcl();
        return self::$acl;
    }

    /**
     * @return Acl
     */
    public static function getAcl() : Acl
    {
        return self::$acl;
    }

    /**
     * @param Acl $acl
     * @return void
     */
    public static function saveAcl(Acl $acl) : void
    {
        self::$acl = $acl;
    }

    /**
     * @return array
     */
    public static function getRoles() : array
    {
        return self::$roles;
    }

    /**
     * @return array
     */
    public static function getResources() : array
    {
        return self::$resources;
    }

    /**
     * Setup application ACL
     * 
     * @return void
     */
    protected static function setupAcl()
    {
        $acl = new Acl();

        self::$roles[self::ACCESS_LEVEL_GUEST] = new Role(self::ACCESS_LEVEL_GUEST);
        self::$roles[self::ACCESS_LEVEL_USER]  = new Role(self::ACCESS_LEVEL_USER);

        self::$resources[self::RESOURCE_APP_FRONT] = new Resource(self::RESOURCE_APP_FRONT);
        self::$resources[self::RESOURCE_APP_BACK]  = new Resource(self::RESOURCE_APP_BACK);

        self::$acl = $acl;
    }
}