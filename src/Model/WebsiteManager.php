<?php

namespace Snowdog\DevTest\Model;

use Snowdog\DevTest\Core\Database;
use Bartwind\SitemapImporter\SitemapImporter;

class WebsiteManager
{
    /**
     * @var Database|\PDO
     */
    private $database;

    /**
     * @var PageManager
     */
    private $pageManager;

    public function __construct(Database $database, PageManager $pageManager)
    {
        $this->database = $database;
        $this->pageManager = $pageManager;
    }
    
    public function getById($websiteId) {
        /** @var \PDOStatement $query */
        $query = $this->database->prepare('SELECT * FROM websites WHERE website_id = :id');
        $query->setFetchMode(\PDO::FETCH_CLASS, Website::class);
        $query->bindParam(':id', $websiteId, \PDO::PARAM_STR);
        $query->execute();
        /** @var Website $website */
        $website = $query->fetch(\PDO::FETCH_CLASS);
        return $website;
    }

    public function getAllByUser(User $user)
    {
        $userId = $user->getUserId();
        /** @var \PDOStatement $query */
        $query = $this->database->prepare('SELECT * FROM websites WHERE user_id = :user');
        $query->bindParam(':user', $userId, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_CLASS, Website::class);
    }

    /**
     * Get Website by hostname
     *
     * @param string $host
     * @return Website|bool
     */
    public function getByHost(string $host)
    {
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare(
            'SELECT
                *
             FROM 
                `websites` w
             WHERE 
                (w.`hostname` = :host)'
        );
        $statement->setFetchMode(\PDO::FETCH_CLASS, Website::class);
        $statement->bindParam(':host', $host, \PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_CLASS);
    }

    /**
     * Import/Update sitemap from uploaded sitemap XML content
     *
     * @param SitemapImporter $sitemapImporter
     * @param User $user
     * @return void
     */
    public function importSitemap(SitemapImporter $sitemapImporter, User $user)
    {
        $sitemapArray = $sitemapImporter->getSitemapArray();
            
            if (!empty($sitemapArray)) {
                foreach ($sitemapArray as $host => $pages) {
                    if (!empty($pages) && !empty($host)) {
                        foreach($pages as $pageUrl) {
                            $website = $this->getByHost($host);
                            
                            if (empty($website)) {
                                $websiteId = $this->create($user, $host, $host);
                                $website = $this->getById($websiteId);
                            } else {
                                $websiteId = $website->getWebsiteId();
                            }

                            $page = $this->pageManager->getByUrlAndWebsiteId($pageUrl, $websiteId);
                            if (empty($page) || !$page) {
                                $this->pageManager->create($website, $pageUrl);
                            }
                        }
                    }
                }
            }
    }

    public function create(User $user, $name, $hostname)
    {
        $userId = $user->getUserId();
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare('INSERT INTO websites (name, hostname, user_id) VALUES (:name, :host, :user)');
        $statement->bindParam(':name', $name, \PDO::PARAM_STR);
        $statement->bindParam(':host', $hostname, \PDO::PARAM_STR);
        $statement->bindParam(':user', $userId, \PDO::PARAM_INT);
        $statement->execute();
        return $this->database->lastInsertId();
    }

}