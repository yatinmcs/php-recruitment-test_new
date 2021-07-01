<?php

use Snowdog\DevTest\Command\MigrateCommand;
use Snowdog\DevTest\Command\WarmCommand;
use Snowdog\DevTest\Command\ImportCommand;
use Snowdog\DevTest\Component\CommandRepository;
use Snowdog\DevTest\Component\Menu;
use Snowdog\DevTest\Component\Migrations;
use Snowdog\DevTest\Component\PermissionRepository;
use Snowdog\DevTest\Component\RouteRepository;
use Snowdog\DevTest\Controller\CreatePageAction;
use Snowdog\DevTest\Controller\CreateWebsiteAction;
use Snowdog\DevTest\Controller\CreateVarnishAction;
use Snowdog\DevTest\Controller\CreateVarnishLinkAction;
use Snowdog\DevTest\Controller\IndexAction;
use Snowdog\DevTest\Controller\LoginAction;
use Snowdog\DevTest\Controller\LoginFormAction;
use Snowdog\DevTest\Controller\LogoutAction;
use Snowdog\DevTest\Controller\RegisterAction;
use Snowdog\DevTest\Controller\RegisterFormAction;
use Snowdog\DevTest\Controller\ImporterAction;
use Snowdog\DevTest\Controller\WebsiteAction;
use Snowdog\DevTest\Controller\VarnishesAction;
use Snowdog\DevTest\Controller\ImportAction;
use Snowdog\DevTest\Menu\ImporterMenu;
use Snowdog\DevTest\Menu\LoginMenu;
use Snowdog\DevTest\Menu\RegisterMenu;
use Snowdog\DevTest\Menu\WebsitesMenu;
use Snowdog\DevTest\Menu\VarnishesMenu;


// ACL setup
$acl = PermissionRepository::createAcl();

$roles = PermissionRepository::getRoles();
$resources = PermissionRepository::getResources();

$guest = $roles[PermissionRepository::ACCESS_LEVEL_GUEST];
$user  = $roles[PermissionRepository::ACCESS_LEVEL_USER];

$front = $resources[PermissionRepository::RESOURCE_APP_FRONT];
$back  = $resources[PermissionRepository::RESOURCE_APP_BACK];

// Guest's scope
$acl->addRule($guest, $front, IndexAction::class, true);
$acl->addRule($guest, $front, LoginFormAction::class, true);
$acl->addRule($guest, $front, LoginAction::class, true);
$acl->addRule($guest, $front, RegisterFormAction::class, true);
$acl->addRule($guest, $front, RegisterAction::class, true);

// User's scope
$acl->addRule($user, $front, IndexAction::class, true);
$acl->addRule($user, $front, LoginFormAction::class, false);
$acl->addRule($user, $front, RegisterFormAction::class, false);
$acl->addRule($user, $back, WebsiteAction::class, true);
$acl->addRule($user, $back, VarnishesAction::class, true);
$acl->addRule($user, $back, ImporterAction::class, true);

// Redirects guest to login page
PermissionRepository::$redirects = [
    IndexAction::class
];

PermissionRepository::saveAcl($acl);

RouteRepository::registerRoute('GET', '/', IndexAction::class, 'execute');
RouteRepository::registerRoute('GET', '/login', LoginFormAction::class, 'execute');
RouteRepository::registerRoute('POST', '/login', LoginAction::class, 'execute');
RouteRepository::registerRoute('GET', '/logout', LogoutAction::class, 'execute');
RouteRepository::registerRoute('GET', '/register', RegisterFormAction::class, 'execute');
RouteRepository::registerRoute('POST', '/register', RegisterAction::class, 'execute');
RouteRepository::registerRoute('GET', '/website/{id:\d+}', WebsiteAction::class, 'execute');
RouteRepository::registerRoute('POST', '/website', CreateWebsiteAction::class, 'execute');
RouteRepository::registerRoute('POST', '/page', CreatePageAction::class, 'execute');
RouteRepository::registerRoute('GET', '/varnishes', VarnishesAction::class, 'execute');
RouteRepository::registerRoute('GET', '/importer', ImporterAction::class, 'execute');
RouteRepository::registerRoute('POST', '/importer/import', ImportAction::class, 'execute');
RouteRepository::registerRoute('POST', '/varnish', CreateVarnishAction::class, 'execute');
RouteRepository::registerRoute('POST', '/varnish/link', CreateVarnishLinkAction::class, 'execute');

Menu::register(LoginMenu::class, 200);
Menu::register(RegisterMenu::class, 250);
Menu::register(WebsitesMenu::class, 4);
Menu::register(VarnishesMenu::class, 5);
Menu::register(ImporterMenu::class, 199);

CommandRepository::registerCommand('migrate_db', MigrateCommand::class);
CommandRepository::registerCommand('warm [id]', WarmCommand::class);
CommandRepository::registerCommand('import [userId] [importSource]', ImportCommand::class);

Migrations::registerComponentMigration('Snowdog\\DevTest', 2);
Migrations::registerComponentMigration('Snowdog\\DevTest', 3);
Migrations::registerComponentMigration('Snowdog\\DevTest', 4);
