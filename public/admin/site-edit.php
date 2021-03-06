<?php

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'smarty.php';

use nntmux\Sites;
use nntmux\SABnzbd;
use nntmux\Category;
use App\Models\Settings;
use nntmux\utility\Utility;

$category = new Category();
$page = new AdminPage();
$sites = new Sites();
$id = 0;

// set the current action
$action = $_REQUEST['action'] ?? 'view';

switch ($action) {
    case 'submit':

        if (! empty($_POST['book_reqids'])) {
            // book_reqids is an array it needs to be a comma separated string, make it so.
            $_POST['book_reqids'] = is_array($_POST['book_reqids']) ?
                implode(', ', $_POST['book_reqids']) : $_POST['book_reqids'];
        }
        $error = '';
        $ret = $page->pdo->settingsUpdate($_POST);
        if (is_int($ret)) {
            if ($ret === Settings::ERR_BADUNRARPATH) {
                $error = 'The unrar path does not point to a valid binary';
            } elseif ($ret === Settings::ERR_BADFFMPEGPATH) {
                $error = 'The ffmpeg path does not point to a valid binary';
            } elseif ($ret === Settings::ERR_BADMEDIAINFOPATH) {
                $error = 'The mediainfo path does not point to a valid binary';
            } elseif ($ret === Settings::ERR_BADNZBPATH) {
                $error = 'The nzb path does not point to a valid directory';
            } elseif ($ret === Settings::ERR_DEEPNOUNRAR) {
                $error = 'Deep password check requires a valid path to unrar binary';
            } elseif ($ret === Settings::ERR_BADTMPUNRARPATH) {
                $error = 'The temp unrar path is not a valid directory';
            } elseif ($ret === Sites::ERR_BADLAMEPATH) {
                $error = 'The lame path is not a valid directory';
            } elseif ($ret === Sites::ERR_SABCOMPLETEPATH) {
                $error = 'The sab complete path is not a valid directory';
            }
        }

        if ($error === '') {
            $site = $ret;
            $returnid = $site['id'];
            header('Location:'.WWW_TOP.'/site-edit.php?id='.$returnid);
        } else {
            $page->smarty->assign('error', $error);
            $site = $sites->row2Object($_POST);
            $page->smarty->assign('site', $site);
        }

        break;
    case 'view':
    default:

        $page->title = 'Site Edit';
        $site = $page->settings;
        $page->smarty->assign('site', $site);
        $page->smarty->assign('settings', Settings::toTree());
        break;
}

$page->smarty->assign('yesno_ids', [1, 0]);
$page->smarty->assign('yesno_names', ['Yes', 'No']);

$page->smarty->assign('passwd_ids', [1, 0]);
$page->smarty->assign('passwd_names', ['Deep (requires unrar)', 'None']);

/*0 = English, 2 = Danish, 3 = French, 1 = German*/
$page->smarty->assign('langlist_ids', [0, 2, 3, 1]);
$page->smarty->assign('langlist_names', ['English', 'Danish', 'French', 'German']);

$page->smarty->assign(
    'imdblang_ids',
    [
        'en', 'da', 'nl', 'fi', 'fr', 'de', 'it', 'tlh', 'no', 'po', 'ru', 'es',
        'sv',
    ]
);
$page->smarty->assign(
    'imdblang_names',
    [
        'English', 'Danish', 'Dutch', 'Finnish', 'French', 'German', 'Italian',
        'Klingon', 'Norwegian', 'Polish', 'Russian', 'Spanish', 'Swedish',
    ]
);

$page->smarty->assign('sabintegrationtype_ids', [SABnzbd::INTEGRATION_TYPE_USER, SABnzbd::INTEGRATION_TYPE_SITEWIDE, SABnzbd::INTEGRATION_TYPE_NONE]);
$page->smarty->assign('sabintegrationtype_names', ['User', 'Site-wide', 'None (Off)']);

$page->smarty->assign('sabapikeytype_ids', [SABnzbd::API_TYPE_NZB, SABnzbd::API_TYPE_FULL]);
$page->smarty->assign('sabapikeytype_names', ['Nzb Api Key', 'Full Api Key']);

$page->smarty->assign('sabpriority_ids', [SABnzbd::PRIORITY_FORCE, SABnzbd::PRIORITY_HIGH, SABnzbd::PRIORITY_NORMAL, SABnzbd::PRIORITY_LOW]);
$page->smarty->assign('sabpriority_names', ['Force', 'High', 'Normal', 'Low']);

$page->smarty->assign('curlproxytype_names', ['', 'HTTP', 'SOCKS5']);

$page->smarty->assign('newgroupscan_names', ['Days', 'Posts']);

$page->smarty->assign('registerstatus_ids', [Settings::REGISTER_STATUS_API_ONLY, Settings::REGISTER_STATUS_OPEN, Settings::REGISTER_STATUS_INVITE, Settings::REGISTER_STATUS_CLOSED]);
$page->smarty->assign('registerstatus_names', ['API Only', 'Open', 'Invite', 'Closed']);

$page->smarty->assign('passworded_ids', [0, 1, 2]);
$page->smarty->assign('passworded_names', [
    'Hide passworded or potentially passworded (*yes)',
    'Hide passworded or potentially passworded (*no)',
    'Show non-passworded and potentially passworded (*no)',
    'Show everything (*no)',
]);

$page->smarty->assign('sphinxrebuildfreqday_days', ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);

$page->smarty->assign('lookuplanguage_iso', ['en', 'de', 'es', 'fr', 'it', 'nl', 'pt', 'sv']);
$page->smarty->assign('lookuplanguage_names', ['English', 'Deutsch', 'Español', 'Français', 'Italiano', 'Nederlands', 'Português', 'Svenska']);

$page->smarty->assign('imdb_urls', [0, 1]);
$page->smarty->assign('imdburl_names', ['imdb.com', 'akas.imdb.com']);

$page->smarty->assign('lookupbooks_ids', [0, 1, 2]);
$page->smarty->assign('lookupbooks_names', ['Disabled', 'Lookup All Books', 'Lookup Renamed Books']);

$page->smarty->assign('lookupgames_ids', [0, 1, 2]);
$page->smarty->assign('lookupgames_names', ['Disabled', 'Lookup All Consoles', 'Lookup Renamed Consoles']);

$page->smarty->assign('lookupmusic_ids', [0, 1, 2]);
$page->smarty->assign('lookupmusic_names', ['Disabled', 'Lookup All Music', 'Lookup Renamed Music']);

$page->smarty->assign('lookupmovies_ids', [0, 1, 2]);
$page->smarty->assign('lookupmovies_names', ['Disabled', 'Lookup All Movies', 'Lookup Renamed Movies']);

$page->smarty->assign('lookuptv_ids', [0, 1, 2]);
$page->smarty->assign('lookuptv_names', ['Disabled', 'Lookup All TV', 'Lookup Renamed TV']);

$page->smarty->assign('lookup_reqids_ids', [0, 1, 2]);
$page->smarty->assign('lookup_reqids_names', ['Disabled', 'Lookup Request IDs', 'Lookup Request IDs Threaded']);

$page->smarty->assign('coversPath', NN_COVERS);

// return a list of audiobooks, mags, ebooks, technical and foreign books
$result = $page->pdo->query("SELECT id, title FROM categories WHERE id IN ({$category->getCategoryValue('MUSIC_AUDIOBOOK')}, {$category->getCategoryValue('BOOKS_MAGAZINES')}, {$category->getCategoryValue('BOOKS_TECHNICAL')}, {$category->getCategoryValue('BOOKS_FOREIGN')})");

// setup the display lists for these categories, this could have been static, but then if names changed they would be wrong
$book_reqids_ids = [];
$book_reqids_names = [];
foreach ($result as $bookcategory) {
    $book_reqids_ids[] = $bookcategory['id'];
    $book_reqids_names[] = $bookcategory['title'];
}

// convert from a string array to an int array as we want to use int
$book_reqids_ids = array_map(create_function('$value', 'return (int)$value;'), $book_reqids_ids);
$page->smarty->assign('book_reqids_ids', $book_reqids_ids);
$page->smarty->assign('book_reqids_names', $book_reqids_names);

// convert from a list to an array as we need to use an array, but teh Settings table only saves strings
$books_selected = explode(',', Settings::settingValue('..book_reqids'));

// convert from a string array to an int array
$books_selected = array_map(create_function('$value', 'return (int)$value;'), $books_selected);
$page->smarty->assign('book_reqids_selected', $books_selected);

$page->smarty->assign('themelist', Utility::getThemesList());

if (strpos(env('NNTP_SERVER'), 'astra') === false) {
    $page->smarty->assign('compress_headers_warning', 'compress_headers_warning');
}

$page->content = $page->smarty->fetch('site-edit.tpl');
$page->render();
