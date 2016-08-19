<?php

if (!defined('_JEXEC')) define( '_JEXEC', 1 );
if (!defined('JPATH_BASE'))  define( 'JPATH_BASE', $_SERVER['DOCUMENT_ROOT'] );

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/framework.php' );

$mainframe = JFactory::getApplication('site');

class user{

	static public function is_manager(){
		$current_user =JFactory::getUser();
		return in_array(6, $current_user->groups);
	}

	static public function is_agent(){
		$current_user =JFactory::getUser();
		return in_array(10, $current_user->groups);
	}

	static public function is_fiz(){
		$current_user =JFactory::getUser();
		return in_array(11, $current_user->groups);
	}

	static public function is_guest(){
		$current_user =JFactory::getUser();

		return $current_user->guest;

	}


	static public function getFee(){


		$current_user =JFactory::getUser();

		$sql = "select fee from aa_agent where user_id = {$current_user->id} limit 1";

		$out = mysqli2::sql2array($sql);

		$out = $out[0];

		return $out['fee'];



	}




}
