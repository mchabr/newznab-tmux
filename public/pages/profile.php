<?php

use nntmux\NZBGet;
use nntmux\SABnzbd;
use App\Models\Settings;
use nntmux\ReleaseComments;

if (! $page->users->isLoggedIn()) {
    $page->show403();
}

$rc = new ReleaseComments;
$sab = new SABnzbd($page);
$nzbget = new NZBGet($page);

$userID = $page->users->currentUserId();
$privileged = $page->users->isAdmin($userID) || $page->users->isModerator($userID);
$privateProfiles = (int) Settings::settingValue('..privateprofiles') === 1;
$publicView = false;

if ($privileged || ! $privateProfiles) {
    $altID = (isset($_GET['id']) && (int) $_GET['id'] >= 0) ? (int) $_GET['id'] : false;
    $altUsername = (isset($_GET['name']) && strlen($_GET['name']) > 0) ? $_GET['name'] : false;

    // If both 'id' and 'name' are specified, 'id' should take precedence.
    if ($altID === false && $altUsername !== false) {
        $user = $page->users->getByUsername($altUsername);
        if ($user) {
            $altID = $user['id'];
            $userID = $altID;
        }
    } elseif ($altID !== false) {
        $userID = $altID;
        $publicView = true;
    }
}

$downloadlist = $page->users->getDownloadRequestsForUser($userID);
$page->smarty->assign('downloadlist', $downloadlist);

$data = $page->users->getById($userID);
if (! $data) {
    $page->show404();
}

// Check if the user selected a theme.
if (! isset($data['style']) || $data['style'] === 'None') {
    $data['style'] = 'Using the admin selected theme.';
}

$offset = $_REQUEST['offset'] ?? 0;
$page->smarty->assign(
    [
        'apirequests'       => $page->users->getApiRequests($userID),
        'grabstoday'        => $page->users->getDownloadRequests($userID),
        'userinvitedby'     => $data['invitedby'] !== '' ? $page->users->getById($data['invitedby']) : '',
        'user'              => $data,
        'privateprofiles'   => $privateProfiles,
        'publicview'        => $publicView,
        'privileged'        => $privileged,
        'pagertotalitems'   => $rc->getCommentCountForUser($userID),
        'pageroffset'       => $offset,
        'pageritemsperpage' => ITEMS_PER_PAGE,
        'pagerquerybase'    => '/profile?id='.$userID.'&offset=',
        'pagerquerysuffix'  => '#comments',
    ]
);

$sabApiKeyTypes = [
    SABnzbd::API_TYPE_NZB => 'Nzb Api Key',
    SABnzbd::API_TYPE_FULL => 'Full Api Key',
];
$sabPriorities = [
    SABnzbd::PRIORITY_FORCE  => 'Force', SABnzbd::PRIORITY_HIGH => 'High',
    SABnzbd::PRIORITY_NORMAL => 'Normal', SABnzbd::PRIORITY_LOW => 'Low',
];
$sabSettings = [1 => 'Site', 2 => 'Cookie'];

// Pager must be fetched after the variables are assigned to smarty.
$page->smarty->assign(
    [
        'pager'         => $page->smarty->fetch('pager.tpl'),
        'commentslist'  => $rc->getCommentsForUserRange($userID, $offset, ITEMS_PER_PAGE),
        'exccats'       => implode(',', $page->users->getCategoryExclusionNames($userID)),
        'saburl'        => $sab->url,
        'sabapikey'     => $sab->apikey,
        'sabapikeytype' => $sab->apikeytype !== '' ? $sabApiKeyTypes[$sab->apikeytype] : '',
        'sabpriority'   => $sab->priority !== '' ? $sabPriorities[$sab->priority] : '',
        'sabsetting'    => $sabSettings[$sab->checkCookie() === true ? 2 : 1],
    ]
);

$page->meta_title = 'View User Profile';
$page->meta_keywords = 'view,profile,user,details';
$page->meta_description = 'View User Profile for '.$data['username'];

$page->content = $page->smarty->fetch('profile.tpl');
$page->render();
