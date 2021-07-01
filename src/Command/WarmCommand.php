<?php

namespace Snowdog\DevTest\Command;

use Snowdog\DevTest\Model\PageManager;
use Snowdog\DevTest\Model\WebsiteManager;
use Snowdog\DevTest\Model\VarnishManager;
use Symfony\Component\Console\Output\OutputInterface;

class WarmCommand
{
    /**
     * @var WebsiteManager
     */
    private $websiteManager;
    /**
     * @var PageManager
     */
    private $pageManager;

    /**
     * @var VarnishManager
     */
    private $varnishManager;

    public function __construct(WebsiteManager $websiteManager, PageManager $pageManager, VarnishManager $varnishManager)
    {
        $this->websiteManager = $websiteManager;
        $this->pageManager = $pageManager;
        $this->varnishManager = $varnishManager;
    }

    public function __invoke($id, OutputInterface $output)
    {
        $website = $this->websiteManager->getById($id);
        if ($website) {
            $pages = $this->pageManager->getAllByWebsite($website);
            $varnishes = $this->varnishManager->getByWebsite($website);

            if (count($varnishes)) {
                foreach($varnishes as $varnish) {
                    $resolver = new \Old_Legacy_CacheWarmer_Resolver_Method();
                    $actor = new \Old_Legacy_CacheWarmer_Actor();
                    $actor->setActor(function ($hostname, $ip, $url, $varnishIp) use ($output) {
                        $output->writeln('Visited <info>http://' . $hostname . '/' . $url . '</info> via IP: <comment>' . $ip . ' @varnish: '.$varnishIp.'</comment>');
                    });

                    $warmer = new \Old_Legacy_CacheWarmer_Warmer();
                    $warmer->setResolver($resolver);
                    $warmer->setHostname($website->getHostname());
                    $warmer->setActor($actor);
                    $warmer->setVarnish($varnish);

                    foreach ($pages as $page) {
                        $warmer->warm($page->getUrl());
                        $this->pageManager->updateVisitTrackTime($page);
                    }
                }
            } else {
                $output->writeln('No varnishes');
            }
        } else {
            $output->writeln('<error>Website with ID ' . $id . ' does not exists!</error>');
        }
    }
}