<?php

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'smarty.php';

use nntmux\Movie;

$page = new AdminPage();
$movie = new Movie();
$id = 0;

// set the current action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    $mov = $movie->getMovieInfo($id);

    if (! $mov) {
        $page->show404();
    }

    switch ($action) {
	    case 'submit':
	    	$coverLoc = WWW_DIR.'covers/movies/'.$id.'-cover.jpg';
	    	$backdropLoc = WWW_DIR.'covers/movies/'.$id.'-backdrop.jpg';

			if ($_FILES['cover']['size'] > 0) {
			    $tmpName = $_FILES['cover']['tmp_name'];
			    $file_info = getimagesize($tmpName);
			    if (! empty($file_info)) {
			        move_uploaded_file($_FILES['cover']['tmp_name'], $coverLoc);
			    }
			}

			if ($_FILES['backdrop']['size'] > 0) {
			    $tmpName = $_FILES['backdrop']['tmp_name'];
			    $file_info = getimagesize($tmpName);
			    if (! empty($file_info)) {
			        move_uploaded_file($_FILES['backdrop']['tmp_name'], $backdropLoc);
			    }
			}

			$_POST['cover'] = (file_exists($coverLoc)) ? 1 : 0;
			$_POST['backdrop'] = (file_exists($backdropLoc)) ? 1 : 0;

			$movie->update([
				'actors'   => $_POST['actors'],
				'backdrop' => $_POST['backdrop'],
				'cover'    => $_POST['cover'],
				'director' => $_POST['director'],
				'genre'    => $_POST['genre'],
				'imdbid'   => $id,
				'language' => $_POST['language'],
				'plot'     => $_POST['plot'],
				'rating'   => $_POST['rating'],
				'tagline'  => $_POST['tagline'],
				'title'    => $_POST['title'],
				'year'     => $_POST['year'],
			]);

			header('Location:'.WWW_TOP.'/movie-list.php');
	        die();
	    break;
	    case 'view':
	    default:
			$page->title = 'Movie Edit';
			$page->smarty->assign('movie', $mov);
		break;
	}
}

$page->content = $page->smarty->fetch('movie-edit.tpl');
$page->render();
