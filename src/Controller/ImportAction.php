<?php

namespace Snowdog\DevTest\Controller;

use Snowdog\DevTest\Model\PageManager;
use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\WebsiteManager;
use Bartwind\SitemapImporter\SitemapImporter;

class ImportAction
{
    const PARAM_IMPORT_TYPE  = 'import_type';
    const PARAM_XML_URL      = 'xml_url';
    const PARAM_XML_FILE     = 'xml_file';
    const PARAM_FILE_TYPE    = 'type';
    const PARAM_FILE_PATH    = 'tmp_name';
    const PARAM_FILE_ERROR   = 'error';

    const IMPORT_TYPE_URL   = 'import_url';
    const IMPORT_TYPE_FILE  = 'import_file';

    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var WebsiteManager
     */
    private $websiteManager;
    /**
     * @var PageManager
     */
    private $pageManager;


    public function __construct(UserManager $userManager, WebsiteManager $websiteManager, PageManager $pageManager)
    {
        $this->userManager = $userManager;
        $this->websiteManager = $websiteManager;
        $this->pageManager = $pageManager;
    }

    /**
     * Import/Update Sitemap XML
     *
     * @return void
     */
    public function execute()
    {
        try {
            $user = $this->userManager->getByLogin($_SESSION['login']);
            if (empty($user)) {
                throw new \Exception('User not found');
            }

            $importType = rtrim($_POST[self::PARAM_IMPORT_TYPE]);
            switch ($importType)
            {
                case self::IMPORT_TYPE_URL:
                    $importSource = $_POST[self::PARAM_XML_URL];
                    if (empty($importSource)) {
                        throw new \Exception('URL is empty');
                    }
            
                    if (!filter_var($importSource, FILTER_VALIDATE_URL)) {
                        throw new \Exception('Invalid URL');
                    }
                    break;

                case self::IMPORT_TYPE_FILE:
                    $importSource = $_FILES[self::PARAM_XML_FILE];
                    if ($importSource[self::PARAM_FILE_TYPE] != 'text/xml') {
                        throw new \Exception('Invalid uploaded file type');
                    }
            
                    if ($importSource[self::PARAM_FILE_ERROR] != 0) {
                        throw new \Exception('File upload error');
                    }

                    $importSource = $importSource[self::PARAM_FILE_PATH];
                    break;

                default:
                    throw new \Exception('Invalid import type');
                    break;
            }

            $sitemapImporter = new SitemapImporter($importSource);
            $this->websiteManager->importSitemap($sitemapImporter, $user);

            $_SESSION['flash'] = 'Sitemap successfully imported!';
        } catch(\Exception $ex) {
            $_SESSION['flash'] = "Import error: " . $ex->getMessage();
        }

        header('Location: /');
    }
} 