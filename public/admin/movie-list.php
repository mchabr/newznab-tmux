<?php

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'smarty.php';

use nntmux\Movie;
use nntmux\utility\Utility;

$page = new AdminPage();
$movie = new Movie(['Settings' => $page->pdo]);

$page->title = 'Movie List';

$movCount = Utility::getCount('movieinfo');

$offset = $_REQUEST['offset'] ?? 0;

$page->smarty->assign([
    'pagertotalitems' => $movCount,
    'pagerquerysuffix'  => '#results',
    'pageroffset' => $offset,
    'pageritemsperpage' => ITEMS_PER_PAGE,
    'pagerquerybase' => WWW_TOP.'/movie-list.php?offset=',
]);
$pager = $page->smarty->fetch('pager.tpl');
$page->smarty->assign('pager', $pager);

$movieList = Utility::getRange('movieinfo', $offset, ITEMS_PER_PAGE);
$page->smarty->assign('movielist', $movieList);

$page->content = $page->smarty->fetch('movie-list.tpl');
$page->render();
