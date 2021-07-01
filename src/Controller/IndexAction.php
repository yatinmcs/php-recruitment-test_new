<?php

namespace Snowdog\DevTest\Controller;

use Snowdog\DevTest\Model\User;
use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\WebsiteManager;
use Snowdog\DevTest\Model\PageManager;
use Snowdog\DevTest\Component\PermissionRepository;

class IndexAction extends AbstractAction
{
    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    /**
     *
     * @var PageManager
     */
    private $pageManager;

    /**
     * @var User
     */
    private $user;

    public function __construct(UserManager $userManager, WebsiteManager $websiteManager, PageManager $pageManager)
    {
        parent::__construct(PermissionRepository::RESOURCE_APP_FRONT);
        $this->websiteManager = $websiteManager;
        $this->pageManager = $pageManager;

        if (isset($_SESSION['login'])) {
            $this->user = $userManager->getByLogin($_SESSION['login']);
        }
    }

    protected function getWebsites()
    {
        if($this->user) {
            return $this->websiteManager->getAllByUser($this->user);
        } 
        return [];
    }

    /**
     * Gets total number of pages associated with user
     *
     * @return int
     */
    protected function getTotalPages()
    {
        if ($user = $this->user) {
            return $this->pageManager->getTotalPagesByUser($user);
        }
        return 0;
    }


    /**
     * Gets user's least recently visited page
     *
     * @return string
     */
    protected function getLeastRecentlyVisitedPage()
    {
        if ($user = $this->user) {
            $page = $this->pageManager->getVisitedByUser($user, PageManager::LEAST_RECENTLY_VISITED_PAGE);
            if ($page && $websiteId = $page->getWebsiteId()) {
                $website = $this->websiteManager->getById($websiteId);
                if ($website) {
                    return $website->getHostname() . '/' . $page->getUrl();
                }
            }
            return null;
        }
        return null;
    }

    /**
     * Gets user's most recently visited page
     *
     * @return string
     */
    protected function getMostRecentlyVisitedPage()
    {
        if ($user = $this->user) {
            $page = $this->pageManager->getVisitedByUser($user, PageManager::MOST_RECENTLY_VISITED_PAGE);
            if ($page && $websiteId = $page->getWebsiteId()) {
                $website = $this->websiteManager->getById($websiteId);
                if ($website) {
                    return $website->getHostname() . '/' . $page->getUrl();
                }
            }
            return null;
        }
        return null;
    }

    public function execute()
    {
        parent::execute();
        require __DIR__ . '/../view/index.phtml';
    }
}