<?php
/**
 * @package     send_custom_header.plugin
 *
 * @copyright   Copyright (C) 2013 B Tasker. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


/**
 * Plugin to send a custom header if specific conditions are met
 *
 */
class plgSystemsend_custom_header extends JPlugin
{

	protected $plugin;
	protected $plgParams;
	var $debug = array();


	function __construct(&$subject, $config){
	    parent::__construct ( $subject, $config );
	    $this->plugin = JPluginHelper::getPlugin ( 'system', 'send_custom_header' );
	    $this->plgParams = new JRegistry ( $this->plugin->params );
	}


	/**
	 * Plugin to send a custom header if specific conditions are met
	 *
	 * @return void;
	 */
	public function onAfterRender()
	{
	    if ($this->processRules()){
		  // OK, we're running so lets get and generate our headers
		  $headers = explode(",",$this->plgParams->get('headerName',''));
      
		  foreach ($headers as $header){
		      if (empty($header)){
			continue;
	 	      }
		      $head = explode("=",$header);
		      header( $head[0].": ".$head[1]);
		  }


		  // Now we do the same for cookies
		  $cookies = explode(",",$this->plgParams->get('cookieVals',''));

		  foreach ($cookies as $cookie){
		      if (empty($cookie)){
			continue;
		      }
		      $cook = explode("=",$cookie);
		      setcookie($cook[0],$cook[1]);
		  }

	      }

	      if ($this->plgParams->get('debugMode',0)){
		  $this->sendDebug();
	      }

	      return true;

	}



	/** Process the rule set to see if we should be sending headers/cookies
	*
	*/
	function processRules(){

	      // Get the rules
	      $alwaysComp = explode(",",$this->plgParams->get('alwaysRun',''));
	      $neverComp = explode(",",$this->plgParams->get('neverRun',''));
	      $alwaysURL = explode(",",$this->plgParams->get('alwaysURL',''));
	      $neverURL = explode(",",$this->plgParams->get('neverURL',''));
	      $when = $this->plgParams->get('sendWhen',1);
	      $sslCons = $this->plgParams->get('runonSSL',2);
	      


	      // Get instances of classes
	      $uri = JUri::getInstance();

	      // Get the current state
	      $app = JFactory::getApplication();
	      $runon = $this->plgParams->get('runonAdmin',2);
	      $isAdmin = $app->isAdmin();
	      $user = JFactory::getUser();
	      $currenturl = $uri->getPath();
	      $currentcomp = JRequest::getVar('option');
	      $isSSL = $uri->isSSL();

	      // Obey the SSL Settings
	      if ($isSSL && $sslCons == "0"){
		$this->debug['Disabled-On-SSL'] = 'True';
		return false;
	      }elseif($isSSL && $sslCons == "1"){
		$this->debug['Force-Enabled-On-SSL'] = 'True';
		return true;
	      }


	      // If an always rule says to run, do so
	      if (in_array($currenturl,$alwaysURL) || in_array($currentcomp,$alwaysComp)){
		  $this->debug['Always-Rule-Applies'] = 'True';
		  return true;
	      }


	      // Process the never rules (including back-end/front-end disablement)
	      if (($isAdmin && $runon == '0') || (!$isAdmin && $runon == '1') || in_array($currenturl,$neverURL) || in_array($currentcomp,$neverComp)){
			$this->debug['Never-Rule-Applies'] = 'True';
			return false;	    
	      }


	      // Check the User based rule
	      if ((!$when && $user->name) || ($when && !$user->name)){
		  $this->debug['User-Login-State-Incorrect'] = 'True';
		  // Either we're supposed to run when user logged in, and they're not, or vice-versa
		  return false;
	      }

	      $this->debug['All-Rules-Satisfied'] = 'True';
	      return true;

	}


	/** Push the contents of the Debug array as headers
	*
	*/
	protected function sendDebug(){
	  foreach($this->debug as $k=>$v){
	    header('X-'.$k.": ".$v);
	  }
	}

}

