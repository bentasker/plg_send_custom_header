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

	      
	      $app = JFactory::getApplication();
	      $runon = $this->plgParams->get('runonAdmin',2);
	      $isAdmin = $app->isAdmin();

	      // Don't run if we've been told not to run on the back-end, or the front-end
	      if (($isAdmin && $runon == '0') || (!$isAdmin && $runon == '1')){
		    return true;
	      }


	      $user = JFactory::getUser();
	      $when = $this->plgParams->get('sendWhen',1);


	      if (($when && $user->name) || (!$when && !$user->name)){
		  // OK, we're running so lets get and generate our header
		  header($this->plgParams->get('headerName','') .":".$this->plgParams->get('headerContent',''));
	      }

	}




}

