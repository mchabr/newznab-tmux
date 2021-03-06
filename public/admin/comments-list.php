<?php

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'smarty.php';

use nntmux\ReleaseComments;

$page = new AdminPage();

$releases = new ReleaseComments($page->pdo);

$page->title = 'Comments List';

$commentcount = $releases->getCommentCount();
$offset = $_REQUEST['offset'] ?? 0;
$page->smarty->assign([
        'pagertotalitems' => $commentcount,
        'pageroffset' => $offset,
        'pageritemsperpage' => ITEMS_PER_PAGE,
        'pagerquerybase' => WWW_TOP.'/comments-list.php?offset=',
        'pagerquerysuffix' => '', ]);
$pager = $page->smarty->fetch('pager.tpl');
$page->smarty->assign('pager', $pager);

$commentslist = $releases->getCommentsRange($offset, ITEMS_PER_PAGE);
$page->smarty->assign('commentslist', $commentslist);

$page->content = $page->smarty->fetch('comments-list.tpl');
$page->render();
