<?php

namespace Snowdog\DevTest\Controller;

use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\VarnishManager;

class CreateVarnishAction
{
    /**
     * @var VarnishManager
     */
    private $varnishManager;

    public function __construct(UserManager $userManager, VarnishManager $varnishManager)
    {
        $this->userManager = $userManager;
        $this->varnishManager = $varnishManager;
    }

    public function execute()
    {
        $ipAddress = $_POST['ip_address'];

        if (isset($_SESSION['login'])) {
            $user = $this->userManager->getByLogin($_SESSION['login']);
            if (empty($ipAddress)) {
                $_SESSION['flash'] = 'IP address cannot be empty!';
            } else {
                if ($this->varnishManager->create($user, $ipAddress)) {
                    $_SESSION['flash'] = 'Varnish ' . $ipAddress . ' added!'; 
                }else{
                    $_SESSION['flash'] = 'IP address already taken.'; 
                }
            }
        }

        header('Location: /varnishes');
    }
}