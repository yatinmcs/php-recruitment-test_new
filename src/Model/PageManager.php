<?php

namespace Snowdog\DevTest\Model;

use DateTime;
use PDO;
use Snowdog\DevTest\Core\Database;

class PageManager
{
    const LEAST_RECENTLY_VISITED_PAGE = 'ASC';
    const MOST_RECENTLY_VISITED_PAGE  = 'DESC';
    const RECENTLY_VISISTED_PAGE_SORT = ['ASC', 'DESC'];

    /**
     * @var Database|\PDO
     */
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Gets Page by ID
     *
     * @param integer $pageId
     * @return Page
     */
    public function getById(int $pageId)
    {
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare(
            'SELECT * FROM `pages` p WHERE `p`.`page_id` = :pageId'
        );
        $statement->bindParam(':pageId', $pageId, \PDO::PARAM_INT);
        $statement->setFetchMode(\PDO::FETCH_CLASS, Page::class);
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_CLASS);
    }

    
    public function getByUrlAndWebsiteId(string $url, int $websiteId)
    {
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare(
            'SELECT
                *
             FROM 
                `pages` p
             WHERE 
                (p.`url` = :url AND p.`website_id` = :websiteId)'
        );
        $statement->setFetchMode(\PDO::FETCH_CLASS, Page::class);
        $statement->bindParam(':url', $url, \PDO::PARAM_STR);
        $statement->bindParam(':websiteId', $websiteId, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_CLASS);
    }

    public function getAllByWebsite(Website $website)
    {
        $websiteId = $website->getWebsiteId();
        /** @var \PDOStatement $query */
        $query = $this->database->prepare('SELECT * FROM pages WHERE website_id = :website');
        $query->bindParam(':website', $websiteId, \PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_CLASS, Page::class);
    }

    public function create(Website $website, $url)
    {
        $websiteId = $website->getWebsiteId();
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare('INSERT INTO pages (url, website_id) VALUES (:url, :website)');
        $statement->bindParam(':url', $url, \PDO::PARAM_STR);
        $statement->bindParam(':website', $websiteId, \PDO::PARAM_INT);
        $statement->execute();
        return $this->database->lastInsertId();
    }

    /**
     * Updates last visited page time track
     *
     * @param Page $page
     * @param DateTime $visitTrackTime
     * @return bool
     */
    public function updateVisitTrackTime(Page $page, DateTime $visitTrackTime = null)
    {
        if (is_null($visitTrackTime) || ($visitTrackTime instanceof DateTime) == FALSE) {
            $visitTrackTime = new DateTime();
        }

        /** @var \PDOStatemant $statement */
        $statement = $this->database->prepare(
            'UPDATE `pages` SET
                `visit_track_time` = :visitTrackTime
            WHERE page_id = :pageId'
        );

        $visitTrackTimeFormatted = $visitTrackTime->format('Y-m-d H:i:s');
        $pageId = $page->getPageId();

        $statement->bindParam(':visitTrackTime', $visitTrackTimeFormatted, \PDO::PARAM_STR);
        $statement->bindParam(':pageId', $pageId, \PDO::PARAM_INT);

        return $statement->execute();
    }

    /**
     * Gets total user pages
     *
     * @param User $user
     * @return int
     */
    public function getTotalPagesByUser(User $user)
    {
        $userId = $user->getUserId(); 
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare(
            'SELECT
                COUNT(*) AS `user_total_pages` 
             FROM
                `pages` p 
             INNER JOIN
                `websites` w 
                ON ( w.`website_id` = p.`website_id` ) 
             WHERE 
                w.`user_id` = :userId'
        );
        $statement->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Gets least/most recently visited page (least by default)
     *
     * @param User $user
     * @param string $sort
     * @return Page
     */
    public function getVisitedByUser(User $user, $sort)
    {
        $userId = $user->getUserId();
        if (!in_array($sort, self::RECENTLY_VISISTED_PAGE_SORT)) {
            $sort = self::LEAST_RECENTLY_VISITED_PAGE;
        }         
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare(
            'SELECT
                p.`page_id` 
             FROM
                `websites` w 
             INNER JOIN
                `pages` p 
                ON (p.`website_id` = w.`website_id`) 
             WHERE
                p.`visit_track_time` IS NOT NULL 
                AND w.`user_id` = :userId 
             ORDER BY
                p.`visit_track_time` ' . $sort
        );

        $statement->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $statement->execute();

        if ($statement->rowCount()) {
            return $this->getById((int)$statement->fetchColumn());
        } else {
            return null;
        }
    }
}