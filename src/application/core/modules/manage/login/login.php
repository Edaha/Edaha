<?php

use Edaha\Entities\User;
use Edaha\Entities\UserSession;

class manage_core_login_login extends kxCmd
{
    public function exec(kxEnv $environment)
    {
        // Wat do????
        switch ($this->request['do']) {
            case 'login':
            default:
                $this->showForm();

                break;

            case 'login-validate':
                $this->loginValidate();

                break;

            case 'logout':
                // $this->logOut();
                break;
        }
    }

    /**
     * Show the login form.
     *
     * @param mixed $message
     */
    public function showForm($message = '')
    {
        $query_string_clean = kxFunc::cleanInputVal(urldecode($_SERVER['QUERY_STRING']));
        // So we can tack that on to the URL afterwards, we get rid of $amp;
        $query_string_clean = str_replace('&amp;', '&', $query_string_clean);
        // Save the old session ID, just in case
        $query_string_clean = str_replace('sid=', 'old_sid=', $query_string_clean);
        // If we were previously in the menu and that for some reason got us here, remove that.
        $query_string_clean = str_replace('module=menu', '', $query_string_clean);

        $twigData['query_string'] = $query_string_clean;
        $twigData['message'] = $message;
        // Let's get the login form.
        kxTemplate::output('manage/login', $twigData);

        // We're done here.
        exit;
    }

    // Set mod cookies for boards
    public function SetModerationCookies()
    {
        // Stub
         /*
  if (isset($_SESSION['manageusername'])) {
  $results = $kx_db->GetAll("SELECT HIGH_PRIORITY `boards` FROM `" . kxEnv::Get('kx:db:prefix') . "staff` WHERE `username` = " . $kx_db->qstr($_SESSION['manageusername']) . " LIMIT 1");
  if ($this->CurrentUserIsAdministrator() || $results[0][0] == 'allboards') {
  setcookie("kumod", "allboards", time() + 3600, kxEnv::Get('kx:paths:boards:folder'), kxEnv::Get('kx:paths:main:domain'));
  } else {
  if ($results[0][0] != '') {
  setcookie("kumod", $results[0][0], time() + 3600, kxEnv::Get('kx:paths:boards:folder'), kxEnv::Get('kx:paths:main:domain'));
  }
  }
  }*/
    }

    // Check login names and create session if user/pass is correct
    private function loginValidate()
    {
        $user = $this->entityManager->getRepository('\Edaha\Entities\User')->findOneBy([
            'username' => $this->request['username'],
        ]);

        if (empty($user)) {
            $this->showForm('Invalid username/password');
        } elseif ($user->checkLogin($this->request['password'])) {
            if (PHP_SESSION_ACTIVE == session_status()) {
                session_regenerate_id();
            } else {
                session_start();
                session_create_id($user->username);
            }

            $user_session = new UserSession($user, session_id());
            $this->entityManager->persist($user_session);
            $this->entityManager->flush();

            $this->request['sid'] = $user_session->sid;

            // Let's figure out where we need to go
            $whereto = '';

            // Unfiltered on purpose
            if ($_POST['qstring']) {
                echo $_POST['qstring'].'<br>';
                $whereto = stripslashes($_POST['qstring']);
                $whereto = str_replace(kxEnv::Get('kx:paths:script:path'), '', $whereto);
                $whereto = str_ireplace('?manage.php', '', $whereto);
                $whereto = ltrim($whereto, '?');
                $whereto = preg_replace('/sid=(\\w){32}/', '', $whereto);
                $whereto = str_replace(['old_&', 'old_&amp;'], '', $whereto);
                $whereto = str_replace('module=login', '', $whereto);
                $whereto = str_replace('section=login', '', $whereto);
                $whereto = str_replace('do=login-validate', '', $whereto);
                $whereto = str_replace('&amp;', '&', $whereto);
                $whereto = preg_replace('/&{1,}/', '&', $whereto);
            }
            $url = kxEnv::Get('kx:paths:script:path').kxEnv::Get('kx:paths:script:folder').'/manage.php?sid='.$user_session->sid.'&'.$whereto;

            kxFunc::doRedirect($url, true);

            exit;
        } else {
            $this->showForm('Invalid username/password');
        }
    }
}
