<?php

namespace Snowdog\DevTest\Controller;

use Snowdog\DevTest\Component\PermissionRepository;

class LoginFormAction extends AbstractAction
{

    public function __construct()
    {
        parent::__construct(PermissionRepository::RESOURCE_APP_FRONT);
    }

    public function execute()
    {
        parent::execute();
        require __DIR__ . '/../view/login.phtml';
    }
}