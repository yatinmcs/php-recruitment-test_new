<?php

namespace Snowdog\DevTest\Command;

use Snowdog\DevTest\Model\WebsiteManager;
use Symfony\Component\Console\Output\OutputInterface;
use Snowdog\DevTest\Model\UserManager;
use Bartwind\SitemapImporter\SitemapImporter;

class ImportCommand
{
    /**
     * @var WebsiteManager
     */
    private $websiteManager;
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(WebsiteManager $websiteManager, UserManager $userManager)
    {
        $this->websiteManager = $websiteManager;
        $this->userManager = $userManager;
    }

    public function __invoke($userId, $importSource, OutputInterface $output)
    {
        try {
            $userId = intval($userId);
            $user = $this->userManager->getById($userId);

            if (empty($user) || !$user) {
                throw new \Exception('User with this ID does not exists');
            }

            if (empty($importSource)) {
                throw new \Exception('Import source arg empty - must be url/path to XML content');
            }
    
            if (!filter_var($importSource, FILTER_VALIDATE_URL)) {
                throw new \Exception('Invalid URL');
            }

            $sitemapImporter = new SitemapImporter($importSource);
            $this->websiteManager->importSitemap($sitemapImporter, $user);
            
            $output->writeln('XML Sitemap imported/updated for UserID: '.$userId);
        } catch (\Exception $ex) {
            $output->writeln('<error>' . $ex->getMessage() . '</error>');
        }
    }
}