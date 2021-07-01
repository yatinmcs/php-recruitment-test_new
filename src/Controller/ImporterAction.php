<?php

namespace Snowdog\DevTest\Controller;

use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Component\PermissionRepository;

class ImporterAction extends AbstractAction
{
    /**
     * @var User
     */
    private $user;

    public function __construct(UserManager $userManager)
    {
        parent::__construct(PermissionRepository::RESOURCE_APP_BACK);
        $this->userManager = $userManager;

        if (isset($_SESSION['login'])) {
            $this->user = $userManager->getByLogin($_SESSION['login']);
        }
    }

    public function execute()
    {
        parent::execute();
        require __DIR__ . '/../view/importer.phtml';
    }
}