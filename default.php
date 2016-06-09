<?php

	$config = dirname(__FILE__) . '/config/config.php';

	//load main config file
	require dirname(__FILE__) . '/config/main.php';

	//create Module
	Module::createModule($config);
