<?php

use nntmux\Movie;
use nntmux\Category;
use nntmux\DnzbFailures;

$movie = new Movie(['Settings' => $page->settings]);
$cat = new Category(['Settings' => $page->settings]);
$fail = new DnzbFailures();

if (! $page->users->isLoggedIn()) {
    $page->show403();
}

$moviecats = $cat->getChildren(Category::MOVIE_ROOT);
$mtmp = [];
foreach ($moviecats as $mcat) {
    $mtmp[$mcat['id']] = $mcat;
}

$category = (isset($_GET['imdb']) ? -1 : Category::MOVIE_ROOT);
if (isset($_REQUEST['t']) && array_key_exists($_REQUEST['t'], $mtmp)) {
    $category = $_REQUEST['t'] + 0;
}

$user = $page->users->getById($page->users->currentUserId());
$cpapi = $user['cp_api'];
$cpurl = $user['cp_url'];
$page->smarty->assign('cpapi', $cpapi);
$page->smarty->assign('cpurl', $cpurl);

$catarray = [];
if ($category != -1) {
    $catarray[] = $category;
}

$page->smarty->assign('catlist', $mtmp);
$page->smarty->assign('category', $category);

$offset = (isset($_REQUEST['offset']) && ctype_digit($_REQUEST['offset'])) ? $_REQUEST['offset'] : 0;
$ordering = $movie->getMovieOrdering();
$orderby = isset($_REQUEST['ob']) && in_array($_REQUEST['ob'], $ordering) ? $_REQUEST['ob'] : '';

$results = $movies = [];
$results = $movie->getMovieRange($catarray, $offset, ITEMS_PER_COVER_PAGE, $orderby, -1, $page->userdata['categoryexclusions']);
foreach ($results as $result) {
    $result['genre'] = makeFieldLinks($result, 'genre', 'movies');
    $result['actors'] = makeFieldLinks($result, 'actors', 'movies');
    $result['director'] = makeFieldLinks($result, 'director', 'movies');
    $result['languages'] = explode(', ', $result['language']);

    $movies[] = $result;
}

$title = (isset($_REQUEST['title']) && ! empty($_REQUEST['title'])) ? stripslashes($_REQUEST['title']) : '';
$page->smarty->assign('title', $title);

$actors = (isset($_REQUEST['actors']) && ! empty($_REQUEST['actors'])) ? stripslashes($_REQUEST['actors']) : '';
$page->smarty->assign('actors', $actors);

$director = (isset($_REQUEST['director']) && ! empty($_REQUEST['director'])) ? stripslashes($_REQUEST['director']) : '';
$page->smarty->assign('director', $director);

$ratings = range(1, 9);
$rating = (isset($_REQUEST['rating']) && in_array($_REQUEST['rating'], $ratings, false)) ? $_REQUEST['rating'] : '';
$page->smarty->assign('ratings', $ratings);
$page->smarty->assign('rating', $rating);

$genres = $movie->getGenres();
$genre = (isset($_REQUEST['genre']) && in_array($_REQUEST['genre'], $genres, false)) ? $_REQUEST['genre'] : '';
$page->smarty->assign('genres', $genres);
$page->smarty->assign('genre', $genre);

$years = range(1903, (date('Y') + 1));
rsort($years);
$year = (isset($_REQUEST['year']) && in_array($_REQUEST['year'], $years, false)) ? $_REQUEST['year'] : '';
$page->smarty->assign('years', $years);
$page->smarty->assign('year', $year);

$browseby_link = '&amp;title='.$title.'&amp;actors='.$actors.'&amp;director='.$director.'&amp;rating='.$rating.'&amp;genre='.$genre.'&amp;year='.$year;

$page->smarty->assign('pagertotalitems', $results[0]['_totalcount'] ?? 0);
$page->smarty->assign('pageroffset', $offset);
$page->smarty->assign('pageritemsperpage', ITEMS_PER_COVER_PAGE);
$page->smarty->assign('pagerquerybase', WWW_TOP.'/movies?t='.$category.$browseby_link.'&amp;ob='.$orderby.'&amp;offset=');
$page->smarty->assign('pagerquerysuffix', '#results');

$pager = $page->smarty->fetch('pager.tpl');
$page->smarty->assign('pager', $pager);

if ($category == -1) {
    $page->smarty->assign('catname', 'All');
} else {
    $cat = new Category();
    $cdata = $cat->getById($category);
    if ($cdata) {
        $page->smarty->assign('catname', $cdata->parent !== null ? $cdata->parent->title.' > '.$cdata->title : $cdata->title);
    } else {
        $page->show404();
    }
}

foreach ($ordering as $ordertype) {
    $page->smarty->assign('orderby'.$ordertype, WWW_TOP.'/movies?t='.$category.$browseby_link.'&ob='.$ordertype.'&offset=0');
}

$page->smarty->assign('results', $movies);

$page->meta_title = 'Browse Nzbs';
$page->meta_keywords = 'browse,nzb,description,details';
$page->meta_description = 'Browse for Nzbs';

if (isset($_GET['imdb'])) {
    $page->content = $page->smarty->fetch('viewmoviefull.tpl');
} else {
    $page->content = $page->smarty->fetch('movies.tpl');
}
$page->render();
