<?php

require_once NN_LIB.'utility'.DS.'SmartyUtils.php';

use nntmux\db\DB;
use nntmux\Users;
use nntmux\SABnzbd;
use App\Models\Settings;

class BasePage
{
    /**
     * @var DB
     */
    public $settings = null;

    /**
     * @var Users
     */
    public $users = null;

    /**
     * @var Smarty
     */
    public $smarty = null;

    public $title = '';
    public $content = '';
    public $head = '';
    public $body = '';
    public $meta_keywords = '';
    public $meta_title = '';
    public $meta_description = '';
    public $secure_connection = false;
    public $show_desktop_mode = false;

    /**
     * Current page the user is browsing. ie browse.
     *
     * @var string
     */
    public $page = '';

    public $page_template = '';

    /**
     * User settings from the MySQL DB.
     *
     * @var array|bool
     */
    public $userdata = [];

    /**
     * URL of the server. ie http://localhost/.
     *
     * @var string
     */
    public $serverurl = '';

    /**
     * Whether to trim white space before rendering the page or not.
     *
     * @var bool
     */
    public $trimWhiteSpace = true;

    /**
     * Is the current session HTTPS?
     *
     * @var bool
     */
    public $https = false;

    /**
     * Public access to Captcha object for error checking.
     *
     * @var \nntmux\Captcha
     */
    public $captcha;

    /**
     * User's theme.
     *
     * @var string
     */
    protected $theme = 'Gentele';

    /**
     * @var string
     */
    public $token;

    /**
     * @var \nntmux\db\DB
     */
    public $pdo;

    /**
     * Set up session / smarty / user variables.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

        if (session_id() === '') {
            session_set_cookie_params(0, '/', '', $this->https, true);
            session_start();
            if (empty($_SESSION['token'])) {
                $_SESSION['token'] = bin2hex(random_bytes(32));
            }
            $this->token = $_SESSION['token'];
        }

        if (NN_FLOOD_CHECK) {
            $this->floodCheck();
        }

        // Buffer settings/DB connection.
        $this->settings = new Settings();
        $this->pdo = new DB();
        $this->smarty = new Smarty();

        $this->smarty->setCompileDir(NN_SMARTY_TEMPLATES);
        $this->smarty->setConfigDir(NN_SMARTY_CONFIGS);
        $this->smarty->setCacheDir(NN_SMARTY_CACHE);
        $this->smarty->setPluginsDir(
            [
                NN_WWW.'plugins/',
                SMARTY_DIR.'plugins/',
            ]
        );
        $this->smarty->error_reporting = (NN_DEBUG ? E_ALL : E_ALL - E_NOTICE);

        if (isset($_SERVER['SERVER_NAME'])) {
            $this->serverurl = (
                ($this->https === true ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].
                (((int) $_SERVER['SERVER_PORT'] !== 80 && (int) $_SERVER['SERVER_PORT'] !== 443) ? ':'.$_SERVER['SERVER_PORT'] : '').
                WWW_TOP.'/'
            );
            $this->smarty->assign('serverroot', $this->serverurl);
        }

        $this->page = $_GET['page'] ?? 'content';

        $this->users = new Users();
        if ($this->users->isLoggedIn()) {
            $this->setUserPreferences();
        } else {
            $this->theme = $this->getSettingValue('site.main.style');

            $this->smarty->assign('isadmin', 'false');
            $this->smarty->assign('ismod', 'false');
            $this->smarty->assign('loggedin', 'false');
        }
        if ($this->theme === 'None') {
            $this->theme = Settings::settingValue('site.main.style');
        }

        $this->smarty->assign('theme', $this->theme);
        $this->smarty->assign('site', $this->settings);
        $this->smarty->assign('page', $this);
    }

    /**
     * Unquotes quoted strings recursively in an array.
     *
     * @param $array
     */
    private function stripSlashes(array &$array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = (is_array($value) ? array_map('stripslashes', $value) : stripslashes($value));
        }
    }

    /**
     * Check if the user is flooding.
     */
    public function floodCheck(): void
    {
        $waitTime = (NN_FLOOD_WAIT_TIME < 1 ? 5 : NN_FLOOD_WAIT_TIME);
        // Check if this is not from CLI.
        if (empty($argc)) {
            // If flood wait set, the user must wait x seconds until they can access a page.
            if (isset($_SESSION['flood_wait_until']) && $_SESSION['flood_wait_until'] > microtime(true)) {
                $this->showFloodWarning($waitTime);
            } else {
                // If user not an admin, they are allowed three requests in FLOOD_THREE_REQUESTS_WITHIN_X_SECONDS seconds.
                if (! isset($_SESSION['flood_check_hits'])) {
                    $_SESSION['flood_check_hits'] = 1;
                    $_SESSION['flood_check_time'] = microtime(true);
                } else {
                    if ($_SESSION['flood_check_hits'] >= (NN_FLOOD_MAX_REQUESTS_PER_SECOND < 1 ? 5 : NN_FLOOD_MAX_REQUESTS_PER_SECOND)) {
                        if ($_SESSION['flood_check_time'] + 1 > microtime(true)) {
                            $_SESSION['flood_wait_until'] = microtime(true) + $waitTime;
                            unset($_SESSION['flood_check_hits']);
                            $this->showFloodWarning($waitTime);
                        } else {
                            $_SESSION['flood_check_hits'] = 1;
                            $_SESSION['flood_check_time'] = microtime(true);
                        }
                    } else {
                        $_SESSION['flood_check_hits']++;
                    }
                }
            }
        }
    }

    /**
     * Done in html here to reduce any smarty processing burden if a large flood is underway.
     *
     * @param int $seconds
     */
    public function showFloodWarning($seconds = 5): void
    {
        header('Retry-After: '.$seconds);
        $this->show503();
    }

    /**
     * Inject content into the html head.
     *
     * @param $headcontent
     */
    public function addToHead($headcontent): void
    {
        $this->head = $this->head."\n".$headcontent;
    }

    /**
     * Inject js/attributes into the html body tag.
     *
     * @param $attr
     */
    public function addToBody($attr): void
    {
        $this->body = $this->body.' '.$attr;
    }

    /**
     * @return bool
     */
    public function isPostBack()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
    }

    /**
     * Show 404 page.
     */
    public function show404(): void
    {
        header('HTTP/1.1 404 Not Found');
        die(view('errors.404'));
    }

    /**
     * Show 403 page.
     *
     * @param bool $from_admin
     */
    public function show403($from_admin = false): void
    {
        header(
            'Location: '.
            ($from_admin ? str_replace('/admin', '', WWW_TOP) : WWW_TOP).
            '/login?redirect='.
            urlencode($_SERVER['REQUEST_URI'])
        );
        exit();
    }

    /**
     * Show 503 page.
     */
    public function show503(): void
    {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        die(view('errors.503'));
    }

    /**
     * Show maintenance page.
     */
    public function showMaintenance(): void
    {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        die(view('errors.maintenance'));
    }

    /**
     * Show Security token mismatch page.
     */
    public function showTokenError(): void
    {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        die(view('errors.tokenError'));
    }

    /**
     * @param string $retry
     */
    public function show429($retry = ''): void
    {
        header('HTTP/1.1 429 Too Many Requests');
        if ($retry !== '') {
            header('Retry-After: '.$retry);
        }

        echo '
			<html>
			<head>
				<title>Too Many Requests</title>
			</head>

			<body>
				<h1>Too Many Requests</h1>

				<p>Wait '.(($retry !== '') ? ceil($retry / 60).' minutes ' : '').'or risk being temporarily banned.</p>

			</body>
			</html>';
        die();
    }

    public function render()
    {
        $this->smarty->display($this->page_template);
    }

    protected function setUserPreferences(): void
    {
        $this->userdata = $this->users->getById($this->users->currentUserId());
        $this->userdata['categoryexclusions'] = $this->users->getCategoryExclusion($this->users->currentUserId());
        $this->userdata['rolecategoryexclusions'] = $this->users->getRoleCategoryExclusion($this->userdata['user_roles_id']);

        // Change the theme to user's selected theme if they selected one, else use the admin one.
        if ((int) Settings::settingValue('site.main.userselstyle') === 1) {
            $this->theme = $this->userdata['style'] ?? 'None';
            if ($this->theme === 'None') {
                $this->theme = Settings::settingValue('site.main.style');
            }
        } else {
            $this->theme = Settings::settingValue('site.main.style');
        }

        // Update last login every 15 mins.
        if ((strtotime($this->userdata['now']) - 900) >
            strtotime($this->userdata['lastlogin'])
        ) {
            $this->users->updateSiteAccessed($this->userdata['id']);
        }

        $this->smarty->assign('userdata', $this->userdata);
        $this->smarty->assign('loggedin', 'true');

        if ($this->userdata['nzbvortex_api_key'] !== '' && $this->userdata['nzbvortex_server_url'] !== '') {
            $this->smarty->assign('weHasVortex', true);
        } else {
            $this->smarty->assign('weHasVortex', false);
        }

        $sab = new SABnzbd($this);
        $this->smarty->assign('sabintegrated', $sab->integratedBool);
        if ($sab->integratedBool !== false && $sab->url !== '' && $sab->apikey !== '') {
            $this->smarty->assign('sabapikeytype', $sab->apikeytype);
        }
        switch ((int) $this->userdata['user_roles_id']) {
            case Users::ROLE_ADMIN:
                $this->smarty->assign('isadmin', 'true');
                break;
            case Users::ROLE_MODERATOR:
                $this->smarty->assign('ismod', 'true');
        }
    }

    /**
     * Allows to fetch a value from the settings table.
     *
     * This method is deprecated, as the column it uses to select the data is due to be removed
     * from the table *soon*.
     *
     * @param $setting
     *
     * @return array|bool|mixed|null|string
     * @throws \Exception
     */
    public function getSetting($setting)
    {
        if (strpos($setting, '.') === false) {
            trigger_error(
                'You should update your template to use the newer method "$page->getSettingValue()"" of fetching values from the "settings" table! This method *will* be removed in a future version.',
                E_USER_WARNING
            );
        } else {
            return $this->getSettingValue($setting);
        }

        return $this->settings->$setting;
    }

    /**
     * @param $setting
     *
     * @return null|string
     * @throws \Exception
     */
    public function getSettingValue($setting): ?string
    {
        return Settings::settingValue($setting);
    }
}
