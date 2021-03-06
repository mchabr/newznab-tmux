<?php

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'smarty.php';

use nntmux\Contents;

$page = new AdminPage();
$contents = new Contents(['Settings' => $page->pdo]);
$contentlist = $contents->getAll();
$page->smarty->assign('contentlist', $contentlist);

$page->title = 'Content List';

$page->content = $page->smarty->fetch('content-list.tpl');
$page->render();
