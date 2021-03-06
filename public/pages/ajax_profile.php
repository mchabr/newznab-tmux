<?php

use App\Models\Settings;

if (! $page->users->isLoggedIn()) {
    $page->show403();
}

if (isset($_GET['action'], $_GET['emailto']) && (int) $_GET['action'] === 1) {
    $emailto = $_GET['emailto'];
    $ret = $page->users->sendInvite(Settings::settingValue('site.main.title'), Settings::settingValue('site.main.email'), $page->serverurl, $page->users->currentUserId(), $emailto);
    if (! $ret) {
        echo 'Invite not sent.';
    } else {
        echo 'Invite sent. Alternatively paste them following link to register - '.$ret;
    }
} else {
    echo 'Invite not sent.';
}
