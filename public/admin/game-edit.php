<?php

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'smarty.php';

use nntmux\Games;
use nntmux\Genres;

$page = new AdminPage();
$games = new Games(['Settings' => $page->pdo]);
$gen = new Genres(['Settings' => $page->pdo]);
$id = 0;

// Set the current action.
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    $game = $games->getGamesInfoById($id);

    if (! $game) {
        $page->show404();
    }

    switch ($action) {
        case 'submit':
            $coverLoc = NN_COVERS.'games/'.$id.'.jpg';

            if ($_FILES['cover']['size'] > 0) {
                $tmpName = $_FILES['cover']['tmp_name'];
                $file_info = getimagesize($tmpName);
                if (! empty($file_info)) {
                    move_uploaded_file($_FILES['cover']['tmp_name'], $coverLoc);
                }
            }

            $_POST['cover'] = (file_exists($coverLoc)) ? 1 : 0;
            $_POST['releasedate'] = (empty($_POST['releasedate']) || ! strtotime($_POST['releasedate'])) ? $game['releasedate'] : date('Y-m-d H:i:s', strtotime($_POST['releasedate']));

            $games->update($id, $_POST['title'], $_POST['asin'], $_POST['url'], $_POST['publisher'], $_POST['releasedate'], $_POST['esrb'], $_POST['cover'], $_POST['trailerurl'], $_POST['genre']);

            header('Location:'.WWW_TOP.'/game-list.php');
            die();
        break;

        case 'view':
        default:
            $page->title = 'Game Edit';
            $page->smarty->assign('game', $game);
            $page->smarty->assign('genres', $gen->getGenres(Genres::GAME_TYPE));
        break;
    }
}

$page->content = $page->smarty->fetch('game-edit.tpl');
$page->render();
