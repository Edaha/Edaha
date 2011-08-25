<?php

class manage_core_login_login extends kxCmd{

  public function exec( kxEnv $environment ) {
    $this->twigData['locale'] = kxEnv::Get('kx:misc:locale');
    $result = $this->db->select('staff', 'stf')
               ->fields('stf', array('user_name'));
    $result->innerJoin("manage_sessions", "ms", "ms.session_staff_id = stf.user_id");
    $this->twigData['name'] = $result->condition('session_id', $this->request['sid'])
                               ->execute()
                               ->fetchField();
    // Wat do????
    switch( $this->request['do'] )
    {
      case 'login':
      default:
        $this->showForm();
      break;
      case 'login-validate':
        $this->loginValidate();
      break;
      case 'logout':
        $this->logOut();
      break;
    }
  }

  /**
   * Show the login form
   *
   * @access	public
   * @return	void
   */
  public function showForm($message="")
  {

    // Kill all old sessions
    $this->db->delete("manage_sessions")
             ->condition("session_last_action", time()-60*60, "<")
             ->execute();

    $query_string_clean = kxFunc::cleanInputVal(urldecode($_SERVER['QUERY_STRING']));
    // So we can tack that on to the URL afterwards, we get rid of $amp;
    $query_string_clean = str_replace('&amp;', '&', $query_string_clean);
    // Save the old session ID, just in case
    $query_string_clean = str_replace('sid=', 'old_sid=', $query_string_clean);
    // If we were previously in the menu and that for some reason got us here, remove that.
    $query_string_clean = str_replace( 'module=menu', '', $query_string_clean );
    
    $this->twigData['query_string'] = $query_string_clean;
    $this->twigData['message']      = $message;
    //Let's get the login form.
    kxTemplate::output("manage/login", $this->twigData);
    // We're done here.
    exit();
  }
  /* Check login names and create session if user/pass is correct */
  public function loginValidate() {

    // Remove old login attempts
    $this->db->delete("loginattempts")
             ->condition("attempt_time", time()-1200, "<")
             ->execute();
    // Are we locked out still?
    $results = $this->db->select("loginattempts")
                        ->fields("loginattempts", array("attempt_ip"))
                        ->condition("attempt_ip", $_SERVER['REMOTE_ADDR'])
                        ->execute()
                        ->fetchAll();
    if (count($results) > 5) {
      kxFunc::showError(_gettext('System lockout'), _gettext('Sorry, because of your numerous failed logins, you have been locked out from logging in for 20 minutes. Please wait and then try again.'));
    } 
    else {
      // Find users with the username supplied to us
      $results = $this->db->select("staff")
                          ->fields("staff", array("user_id", "user_name", "user_password", "user_salt"))
                          ->condition("user_name", $this->request['username'])
                          ->execute()
                          ->fetchAll();
      if (count($results) > 0) {
        if (md5($this->request['password'] . $results[0]->user_salt) == $results[0]->user_password) {

          // Let's make our session
          $session_id = md5(uniqid(microtime()));
          $this->request['sid'] =  $session_id;
          
          // Delete any sessions that already exist for this user
          $this->db->delete("manage_sessions")
                   ->condition("session_staff_id", $results[0]->user_id)
                   ->execute();
          
          // Insert our new values
          $this->db->insert("manage_sessions")
                   ->fields(array(
                     'session_id'          => $session_id,
                     'session_ip'          => $_SERVER['REMOTE_ADDR'],
                     'session_staff_id'    => $results[0]->user_id,
                     'session_location'    => "index",
                     'session_log_in_time' => time(),
                     'session_last_action' => time(),
                     'session_url'         => ""
                   ))
                   ->execute();

          // Set the cookies so ajax functions will load    
          $this->SetModerationCookies();

          //$this->environment->get('kx:classes:core:logging:id')->manageLog(_gettext('Logged in'), 1);
          
          // Let's figure out where we need to go
          $whereto = "";

          // Unfiltered on purpose
          if ($_POST['qstring']) {
            $whereto = stripslashes($_POST['qstring']);
            $whereto = str_replace(kxEnv::Get('kx:paths:script:path'), "", $whereto);
            $whereto = str_ireplace("?manage.php", "" , $whereto);
            $whereto = ltrim($whereto, '?');
            $whereto = preg_replace("/sid=(\w){32}/", "", $whereto);
            $whereto = str_replace( array( 'old_&', 'old_&amp;' ), "", $whereto );
            $whereto = str_replace("module=login", "", $whereto );
            $whereto = str_replace("do=login-validate", "", $whereto );
            $whereto = str_replace('&amp;', '&', $whereto );
            $whereto = preg_replace( "/&{1,}/", "&", $whereto );
          }
          $url = kxEnv::Get('kx:paths:script:path') . kxEnv::Get('kx:paths:script:folder').'/manage.php?sid=' . $session_id . '&' . $whereto;
          if(!empty($_COOKIE['use_frames'])) {
            $this->twigData['url'] = $url;
            kxTemplate::output("manage/frames", $this->twigData);
          }
          else {
            kxFunc::doRedirect($url, true);
          }
          exit();
        } 
        else {
          $this->db->insert("loginattempts")
                   ->fields(array(
                     'attempt_name' => $this->request['username'],
                     'attempt_ip'   => $_SERVER['REMOTE_ADDR'],
                     'attempt_time' => time()
                   ))
                   ->execute();
          $this->showForm(_gettext('Incorrect username/password.'));
        }
      } 
      else {
        $this->db->insert("loginattempts")
                   ->fields(array(
                     'attempt_name' => $this->request['username'],
                     'attempt_ip'   => $_SERVER['REMOTE_ADDR'],
                     'attempt_time' => time()
                   ))
                   ->execute();
        $this->showForm(_gettext('Incorrect username/password.'));
      }
    }
  }
	/* Set mod cookies for boards */
	public function SetModerationCookies() {
    // Stub
    return;/*
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
}