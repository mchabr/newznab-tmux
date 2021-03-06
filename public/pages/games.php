<?php

use nntmux\Games;
use nntmux\Genres;
use nntmux\Category;
use nntmux\DnzbFailures;

if (! $page->users->isLoggedIn()) {
    $page->show403();
}

$games = new Games(['Settings' => $page->settings]);
$cat = new Category(['Settings' => $page->settings]);
$gen = new Genres(['Settings' => $page->settings]);
$fail = new DnzbFailures();

$concats = $cat->getChildren(Category::PC_ROOT);
$ctmp = [];
foreach ($concats as $ccat) {
    $ctmp[$ccat['id']] = $ccat;
}
$category = Category::PC_GAMES;
if (isset($_REQUEST['t']) && array_key_exists($_REQUEST['t'], $ctmp)) {
    $category = $_REQUEST['t'] + 0;
}

$catarray = [];
$catarray[] = $category;

$page->smarty->assign('catlist', $ctmp);
$page->smarty->assign('category', $category);

$offset = (isset($_REQUEST['offset']) && ctype_digit($_REQUEST['offset'])) ? $_REQUEST['offset'] : 0;
$ordering = $games->getGamesOrdering();

$orderby = isset($_REQUEST['ob']) && in_array($_REQUEST['ob'], $ordering, false) ? $_REQUEST['ob'] : '';

$results = $games2 = [];
$results = $games->getGamesRange($catarray, $offset, ITEMS_PER_COVER_PAGE, $orderby, '', $page->userdata['categoryexclusions']);
$maxwords = 50;
foreach ($results as $result) {
    if (! empty($result['review'])) {
        // remove "Overview" from start of review if present
        if (0 === strpos($result['review'], 'Overview')) {
            $result['review'] = substr($result['review'], 8);
        }
        $words = explode(' ', $result['review']);
        if (count($words) > $maxwords) {
            $newwords = array_slice($words, 0, $maxwords);
            $result['review'] = implode(' ', $newwords).'...';
        }
    }
    $games2[] = $result;
}

$title = (isset($_REQUEST['title']) && ! empty($_REQUEST['title'])) ? stripslashes($_REQUEST['title']) : '';
$page->smarty->assign('title', $title);

$genres = $gen->getGenres(Genres::GAME_TYPE, true);
$tmpgnr = [];
foreach ($genres as $gn) {
    $tmpgnr[$gn['id']] = $gn['title'];
}

$years = range(1903, date('Y') + 1);
rsort($years);
$year = (isset($_REQUEST['year']) && in_array($_REQUEST['year'], $years, false)) ? $_REQUEST['year'] : '';
$page->smarty->assign('years', $years);
$page->smarty->assign('year', $year);

$genre = (isset($_REQUEST['genre']) && array_key_exists($_REQUEST['genre'], $tmpgnr)) ? $_REQUEST['genre'] : '';
$page->smarty->assign('genres', $genres);
$page->smarty->assign('genre', $genre);

$browseby_link = '&amp;title='.$title.'&amp;year='.$year;

$page->smarty->assign('pagertotalitems', $results[0]['_totalcount'] ?? 0);
$page->smarty->assign('pageroffset', $offset);
$page->smarty->assign('pageritemsperpage', ITEMS_PER_COVER_PAGE);
$page->smarty->assign('pagerquerybase', WWW_TOP.'/games?t='.$category.$browseby_link.'&amp;ob='.$orderby.'&amp;offset=');
$page->smarty->assign('pagerquerysuffix', '#results');

$pager = $page->smarty->fetch('pager.tpl');
$page->smarty->assign('pager', $pager);

if ($category == -1) {
    $page->smarty->assign('catname', 'All');
} else {
    $cdata = $cat->getById($category);
    if ($cdata) {
        $page->smarty->assign('catname', $cdata->parent !== null ? $cdata->parent->title.' > '.$cdata->title : $cdata->title);
    } else {
        $page->show404();
    }
}

foreach ($ordering as $ordertype) {
    $page->smarty->assign('orderby'.$ordertype, WWW_TOP.'/games?t='.$category.$browseby_link.'&amp;ob='.$ordertype.'&amp;offset=0');
}

$page->smarty->assign('results', $games2);

$page->meta_title = 'Browse Games';
$page->meta_keywords = 'browse,nzb,games,description,details';
$page->meta_description = 'Browse for Games';

$page->content = $page->smarty->fetch('games.tpl');
$page->render();
