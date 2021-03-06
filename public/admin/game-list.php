<?php

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'smarty.php';

use nntmux\Games;

$page = new AdminPage();
$game = new Games(['Settings' => $page->pdo]);

$page->title = 'Game List';

$gameCount = $game->getCount();

$offset = $_REQUEST['offset'] ?? 0;

$page->smarty->assign([
    'pagertotalitems' => $gameCount,
    'pagerquerysuffix'  => '#results',
    'pageroffset' => $offset,
    'pageritemsperpage' => ITEMS_PER_PAGE,
    'pagerquerybase' => WWW_TOP.'/game-list.php?offset=',
]);

$pager = $page->smarty->fetch('pager.tpl');
$page->smarty->assign('pager', $pager);

$gamelist = $game->getRange($offset, ITEMS_PER_PAGE);

$page->smarty->assign('gamelist', $gamelist);

$page->content = $page->smarty->fetch('game-list.tpl');
$page->render();
