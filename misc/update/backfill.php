<?php

require_once dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'bootstrap/autoload.php';

use nntmux\NNTP;
use nntmux\db\DB;
use nntmux\Backfill;

$pdo = new DB();

// Create the connection here and pass
$nntp = new NNTP(['Settings' => $pdo]);
if ($nntp->doConnect() !== true) {
    exit($pdo->log->error('Unable to connect to usenet.'));
}

if (isset($argv[1]) && $argv[1] === 'all' && ! isset($argv[2])) {
    $backfill = new Backfill(['NNTP' => $nntp, 'Settings' => $pdo]);
    $backfill->backfillAllGroups();
} elseif (isset($argv[1]) && ! isset($argv[2]) && preg_match('/^alt\.binaries\..+$/i', $argv[1])) {
    $backfill = new Backfill(['NNTP' => $nntp, 'Settings' => $pdo]);
    $backfill->backfillAllGroups($argv[1]);
} elseif (isset($argv[1], $argv[2]) && is_numeric($argv[2]) && preg_match('/^alt\.binaries\..+$/i', $argv[1])) {
    $backfill = new Backfill(['NNTP' => $nntp, 'Settings' => $pdo]);
    $backfill->backfillAllGroups($argv[1], $argv[2]);
} elseif (isset($argv[1], $argv[2]) && $argv[1] === 'alph' && is_numeric($argv[2])) {
    $backfill = new Backfill(['NNTP' => $nntp, 'Settings' => $pdo]);
    $backfill->backfillAllGroups('', $argv[2], 'normal');
} elseif (isset($argv[1], $argv[2]) && $argv[1] === 'date' && is_numeric($argv[2])) {
    $backfill = new Backfill(['NNTP' => $nntp, 'Settings' => $pdo]);
    $backfill->backfillAllGroups('', $argv[2], 'date');
} elseif (isset($argv[1], $argv[2]) && $argv[1] === 'safe' && is_numeric($argv[2])) {
    $backfill = new Backfill(['NNTP' => $nntp, 'Settings' => $pdo]);
    $backfill->safeBackfill($argv[2]);
} else {
    exit(\nntmux\ColorCLI::error("\nWrong set of arguments.\n"
            .'php backfill.php safe 200000		 ...: Backfill an active group alphabetically, x articles, the script stops,'."\n"
            .'					 ...: if the group has reached reached 2012-06-24, the next group will backfill.'."\n"
            .'php backfill.php alph 200000 		 ...: Backfills all groups (sorted alphabetically) by number of articles'."\n"
            .'php backfill.php date 200000 		 ...: Backfills all groups (sorted by least backfilled in time) by number of articles'."\n"
            .'php backfill.php alt.binaries.ath 200000 ...: Backfills a group by name by number of articles'."\n"
            .'php backfill.php all			 ...: Backfills all groups 1 at a time, by date (set in admin-view groups)'."\n"
            .'php backfill.php alt.binaries.ath	 ...: Backfills a group by name, by date (set in admin-view groups)'."\n"));
}
