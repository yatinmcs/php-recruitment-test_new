<?php

namespace Snowdog\DevTest\Controller;

use SimpleAcl\Acl;
use Snowdog\DevTest\Component\PermissionRepository;
use Snowdog\DevTest\Controller\IndexAction;

abstract class AbstractAction
{
    protected static $accessLevel;
    protected static $resourceName;
    protected $routeClass;

    public function __construct(string $resourceName)
    {
        $calledClass = get_called_class();
        $this->setRouteClass($calledClass);
        self::setResourceName($resourceName);
    }

    /**
     * @return string
     */
    public function getRouteClass()
    {
        return $this->routeClass;
    }

    /**
     * @param string $class
     * @return void
     */
    public function setRouteClass(string $class) : void
    {
        $this->routeClass = $class;
    }

    public static function getResourceName()
    {
        return self::$resourceName;
    }

    public static function setResourceName(string $resourceName)
    {
        self::$resourceName = $resourceName;
    }

    /**
     * @return string
     */
    public static function getAccessLevel() : string
    {
        return self::$accessLevel;
    }

    /**
     * @param string $accessLevel
     * @return void
     */
    public static function setAccessLevel(string $accessLevel) : void
    {
        self::$accessLevel = $accessLevel;
    }

    public function execute()
    {
        $acl = PermissionRepository::getAcl();
        $userState = PermissionRepository::getUserState();
        $routeClass = $this->getRouteClass();
        $resourceName = self::getResourceName();

        $isAllowed = $acl->isAllowed($userState, $resourceName, $routeClass);

        if (in_array($routeClass, PermissionRepository::$redirects) && $userState == PermissionRepository::ACCESS_LEVEL_GUEST) {
            header('Location: /login');
            return;
        }

        if ($isAllowed === false) {
            header('HTTP/1.0 403 Forbidden');
            require __DIR__ . '/../view/403.phtml';
            die;
        }
    }

}