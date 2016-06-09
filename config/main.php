<?php


class Module{

	public static function createModule($configFile){
	
		//get $config file array
		$configArray = include $configFile; 

		$config = json_decode( json_encode($configArray), false );

		//Append this config to application config
		App::$config->module = $config;
		
		//run session if autoStart is true
		if( App::$config->module->session->autoStart && App::$config->session->autoStart === false ) session_start();

		//set include path
		$include = '';
		for($i=0; $i<=count(App::$config->module->includes)-1; $i++){
			$include .= App::$config->module->includes[$i];
			if( $i<count(App::$config->module->includes)-1 ) $include .= PATH_SEPARATOR;
		}
		set_include_path( $include );

		//run controller and action
		$controller = (!isset($_REQUEST['r'])) ? App::$config->module->controller->default : $_REQUEST['r'];
		$action = (!isset($_REQUEST['a'])) ? 'index' : $_REQUEST['a'];

		//get controller id
		App::$controllerId = $controller;
		//get action id
		App::$actionId = $action;

		//imports...
		App::importFiles(App::$config->module->imports);

		$class = App::$controllerId . 'Controller';

		//check controller...
		if( class_exists($class) ) 
			$controller = new $class();
		else
            header('Location: ' . App::$config->baseUrl . '/errors/index?er=404');

		//run before action function if exists
		if( method_exists($controller, 'beforeAction') )
			$controller->beforeAction();

		//run action
		if( method_exists($controller, 'action'.App::$actionId) ) 
			$controller->{'action'.App::$actionId}();
		else
			$controller->redirect('index');

	}
}