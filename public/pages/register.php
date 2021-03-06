<?php

use nntmux\Users;
use nntmux\Captcha;
use App\Models\Settings;
use nntmux\utility\Utility;

if ($page->users->isLoggedIn()) {
    header('Location: '.WWW_TOP.'/');
}

$error = $userName = $password = $confirmPassword = $email = $inviteCode = $inviteCodeQuery = '';
$showRegister = 1;

if ((int) Settings::settingValue('..registerstatus') === Settings::REGISTER_STATUS_CLOSED || (int) Settings::settingValue('..registerstatus') === Settings::REGISTER_STATUS_API_ONLY) {
    $error = 'Registrations are currently disabled.';
    $showRegister = 0;
} elseif (Settings::settingValue('..registerstatus') === Settings::REGISTER_STATUS_INVITE && (! isset($_REQUEST['invitecode']) || empty($_REQUEST['invitecode']))) {
    $error = 'Registrations are currently invite only.';
    $showRegister = 0;
}

if ($showRegister === 1) {
    $action = $_REQUEST['action'] ?? 'view';

    //Be sure to persist the invite code in the event of multiple form submissions. (errors)
    if (isset($_REQUEST['invitecode'])) {
        $inviteCodeQuery = '&invitecode='.$_REQUEST['invitecode'];
    }

    $captcha = new Captcha($page);

    switch ($action) {
        case 'submit':
            if ($captcha->getError() === false) {
                if (Utility::checkCsrfToken() === true) {
                    $userName = $_POST['username'];
                    $password = $_POST['password'];
                    $confirmPassword = $_POST['confirmpassword'];
                    $email = $_POST['email'];
                    if (! empty($_REQUEST['invitecode'])) {
                        $inviteCode = $_REQUEST['invitecode'];
                    }

                    // Check uname/email isn't in use, password valid. If all good create new user account and redirect back to home page.
                    if ($password !== $confirmPassword) {
                        $error = 'Password Mismatch';
                    } else {
                        // Get the default user role.
                        $userDefault = $page->users->getDefaultRole();

                        $ret = $page->users->signup(
                            $userName,
                            $password,
                            $email,
                            $_SERVER['REMOTE_ADDR'],
                            $userDefault['id'],
                            '',
                            $userDefault['defaultinvites'],
                            $inviteCode
                        );

                        if ($ret > 0) {
                            $page->users->login($ret, $_SERVER['REMOTE_ADDR']);
                            header('Location: '.WWW_TOP.'/');
                        } else {
                            switch ($ret) {
                                case Users::ERR_SIGNUP_BADUNAME:
                                    $error = 'Your username must be at least five characters.';
                                    break;
                                case Users::ERR_SIGNUP_BADPASS:
                                    $error = 'Your password must be longer than eight characters.';
                                    break;
                                case Users::ERR_SIGNUP_BADEMAIL:
                                    $error = 'Your email is not a valid format.';
                                    break;
                                case Users::ERR_SIGNUP_UNAMEINUSE:
                                    $error = 'Sorry, the username is already taken.';
                                    break;
                                case Users::ERR_SIGNUP_EMAILINUSE:
                                    $error = 'Sorry, the email is already in use.';
                                    break;
                                case Users::ERR_SIGNUP_BADINVITECODE:
                                    $error = 'Sorry, the invite code is old or has been used.';
                                    break;
                                default:
                                    $error = 'Failed to register.';
                                    break;
                            }
                        }
                    }
                } else {
                    $page->showTokenError();
                }
            }
            break;
        case 'view': {
            $inviteCode = $_GET['invitecode'] ?? null;
            if (isset($inviteCode)) {
                // See if it is a valid invite.
                $invite = $page->users->getInvite($inviteCode);
                if (! $invite) {
                    $error = sprintf('Bad or invite code older than %d days.', Users::DEFAULT_INVITE_EXPIRY_DAYS);
                    $showRegister = 0;
                } else {
                    $inviteCode = $invite['guid'];
                }
            }
            break;
        }
    }
}
$page->smarty->assign(
    [
        'username'          => Utility::htmlfmt($userName),
        'password'          => Utility::htmlfmt($password),
        'confirmpassword'   => Utility::htmlfmt($confirmPassword),
        'email'             => Utility::htmlfmt($email),
        'invitecode'        => Utility::htmlfmt($inviteCode),
        'invite_code_query' => Utility::htmlfmt($inviteCodeQuery),
        'showregister'      => $showRegister,
        'error'             => $error,
        'csrf_token'        => $page->token,
    ]
);
$page->meta_title = 'Register';
$page->meta_keywords = 'register,signup,registration';
$page->meta_description = 'Register';

$page->content = $page->smarty->fetch('register.tpl');
$page->render();
