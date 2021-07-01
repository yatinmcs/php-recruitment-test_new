<?php

namespace Snowdog\DevTest\Menu;

class ImporterMenu extends AbstractMenu
{

    public function isActive()
    {
        return $_SERVER['REQUEST_URI'] == '/importer';
    }

    public function getHref()
    {
        return '/importer';
    }

    public function getLabel()
    {
        return 'Importer';
    }
}