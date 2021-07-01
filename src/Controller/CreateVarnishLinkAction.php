<?php

namespace Snowdog\DevTest\Controller;

use Exception;
use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\Varnish;
use Snowdog\DevTest\Model\VarnishManager;
use Snowdog\DevTest\Model\Website;
use Snowdog\DevTest\Model\WebsiteManager;

class CreateVarnishLinkAction
{
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var VarnishManager
     */
    private $varnishManager;
    /**
     *
     * @var WebsiteManager
     */
    private $websiteManager;

    public function __construct(UserManager $userManager, VarnishManager $varnishManager, WebsiteManager $websiteManager)
    {
        $this->userManager = $userManager;
        $this->varnishManager = $varnishManager;
        $this->websiteManager = $websiteManager;
    }

    public function execute()
    {
        $varnishId = (int)$_POST['varnishId'];
        $websiteId = (int)$_POST['websiteId'];
        $isChecked = ($_POST['isChecked'] == 'true') ? TRUE : FALSE;
        
        try {
            $varnish = $this->varnishManager->getById($varnishId);
            $website = $this->websiteManager->getById($websiteId);

            if (empty($varnish) || empty($website)) {
                throw new \Exception("No Varnish/Website");
            }

            if ($isChecked) {
                $result = $this->varnishManager->link($varnishId, $websiteId);
            } else {
                $this->varnishManager->unlink($varnishId, $websiteId);
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Varnish website saved'
            ]);
            die;
        } catch (\Exception $ex) {
            echo json_encode([
                'status' => 'error',
                'message' => $ex->getMessage()
            ]);
            die;
        }
    }
}