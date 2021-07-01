<?php

namespace Snowdog\DevTest\Migration;

use Snowdog\DevTest\Core\Database;

class Version4
{
    /**
     * @var Database|\PDO
     */
    private $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function __invoke()
    {
        $this->createVarnishesTable();
        $this->createVarnishesWebsitesTable();
    }

    private function createVarnishesTable()
    {
        $createQuery = <<<SQL
CREATE TABLE `varnishes` (
    `varnish_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_address` varchar(100) UNIQUE NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`varnish_id`),
    CONSTRAINT `varnish_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $this->database->exec($createQuery);
    }

    private function createVarnishesWebsitesTable()
    {
        $createQuery = <<<SQL
CREATE TABLE `varnishes_websites` (
    `varnish_id` INT(11) UNSIGNED NOT NULL,
    `website_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`varnish_id`, `website_id`),
    CONSTRAINT `varnish_varnish_fk` FOREIGN KEY (`varnish_id`) REFERENCES `varnishes` (`varnish_id`),
    CONSTRAINT `varnish_website_fk` FOREIGN KEY (`website_id`) REFERENCES `websites` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $this->database->exec($createQuery);
    }
}