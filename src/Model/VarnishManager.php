<?php

namespace Snowdog\DevTest\Model;

use Snowdog\DevTest\Core\Database;
use Snowdog\DevTest\Model\WebsiteManager;

class VarnishManager
{

    /**
     * @var Database|\PDO
     */
    private $database;

    private $websiteManager;

    public function __construct(Database $database, WebsiteManager $websiteManager)
    {
        $this->database = $database;
        $this->websiteManager = $websiteManager;
    }

    public function getById(int $id)
    {
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare(
            'SELECT
                * 
             FROM `varnishes` v
             WHERE 
                v.`varnish_id` = :id'
        );
        $statement->setFetchMode(\PDO::FETCH_CLASS, Varnish::class);
        $statement->bindParam(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        $user = $statement->fetch(\PDO::FETCH_CLASS);

        return $user;
    }

    public function getAllByUser(User $user)
    {
        $userId = $user->getUserId();
        /** @var \PDOStatement $query */
        $query = $this->database->prepare(
            'SELECT
                v.`varnish_id`, INET_NTOA(v.`ip_address`) AS `ip_address`,
                user_id
            FROM `varnishes` v
            WHERE user_id = :userId'
        );
        $query->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_CLASS, Varnish::class);
    }

    public function getWebsites(Varnish $varnish)
    {
        $varnishId = $varnish->getVarnishId();
        $query = $this->database->prepare(
            'SELECT
                *
             FROM
                `websites` w
             INNER JOIN `varnishes_websites` vw 
                ON (vw.`website_id` = w.`website_id`)
             WHERE 
                vw.`varnish_id` = :varnishId');
        $query->bindParam(':varnishId', $varnishId, \PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_CLASS, Website::class);
    }

    public function getByWebsite(Website $website)
    {
        $websiteId = $website->getWebsiteId();

        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare(
            'SELECT 
                v.`varnish_id`, INET_NTOA(v.`ip_address`) AS `ip_address`,
                v.`user_id`
             FROM 
                `varnishes` v
             INNER JOIN 
                `varnishes_websites` vw 
                ON (vw.`varnish_id` = v.`varnish_id`)
             WHERE vw.`website_id` = :websiteId'
        );
        $statement->bindParam(':websiteId', $websiteId, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_CLASS, Varnish::class);
    }

    public function create(User $user, string $ipAddress)
    {
        $userId = $user->getUserId();
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare(
            'INSERT INTO 
                varnishes (ip_address, user_id) 
            VALUES 
                (INET_ATON(:ip_address), :userId)'
        );
        $statement->bindParam(':ip_address', $ipAddress, \PDO::PARAM_STR);
        $statement->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $statement->execute();

        return $this->database->lastInsertId();
    }

    public function link(int $varnishId, int $websiteId)
    {
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare(
            'INSERT INTO 
                `varnishes_websites` 
                    (`varnish_id`, `website_id`) 
             VALUES (:varnishId, :websiteId)'
        );
        $statement->bindParam(':varnishId', $varnishId, \PDO::PARAM_INT);
        $statement->bindParam(':websiteId', $websiteId, \PDO::PARAM_INT);

        return $statement->execute();
    }

    public function unlink(int $varnishId, int $websiteId)
    {
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare(
            'DELETE FROM 
                `varnishes_websites`
             WHERE 
                ((`varnish_id` = :varnishId) AND (`website_id` = :websiteId))'
        );
        $statement->bindParam(':varnishId', $varnishId, \PDO::PARAM_INT);
        $statement->bindParam(':websiteId', $websiteId, \PDO::PARAM_INT);

        return $statement->execute();
    }

}