<?php

//This script will update all records in the gamesinfo table

require_once dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'bootstrap/autoload.php';

use nntmux\db\DB;
use nntmux\Games;
use nntmux\ColorCLI;

$pdo = new DB();
$game = new Games(['Echo' => true, 'Settings' => $pdo]);

$res = $pdo->query(
    sprintf('SELECT id, title FROM gamesinfo WHERE cover = 0 ORDER BY id DESC LIMIT 100')
);
$total = count($res);
if ($total > 0) {
    echo ColorCLI::header('Updating game covers for '.number_format($total).' releases.');

    foreach ($res as $arr) {
        $starttime = microtime(true);
        $gameInfo = $game->parseTitle($arr['title']);
        if ($gameInfo !== false) {
            echo ColorCLI::primary('Looking up: '.$gameInfo['release']);
            $gameData = $game->updateGamesInfo($gameInfo);
            if ($gameData === false) {
                echo ColorCLI::primary($gameInfo['release'].' not found');
            } else {
                if (file_exists(NN_COVERS.'games'.DS.$gameData.'.jpg')) {
                    $pdo->queryExec(sprintf('UPDATE gamesinfo SET cover = 1 WHERE id = %d', $arr['id']));
                }
            }
        }

        // amazon limits are 1 per 1 sec
        $diff = floor((microtime(true) - $starttime) * 1000000);
        if (1000000 - $diff > 0) {
            echo ColorCLI::alternate('Sleeping');
            usleep(1000000 - $diff);
        }
    }
}
