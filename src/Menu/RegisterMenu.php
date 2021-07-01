<?php

namespace Snowdog\DevTest\Menu;

class RegisterMenu extends AbstractMenu
{

    public function isActive()
    {
        return $_SERVER['REQUEST_URI'] == '/register';
    }

    public function getHref()
    {
        return '/register';
    }

    public function getLabel()
    {
        return 'Register';
    }

    public function __invoke()
    {
        if (!isset($_SESSION['login'])) {
            parent::__invoke();
        }
    }
}