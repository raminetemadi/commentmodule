<?php

/* Application config */
return array(
	
			//base path for client side
			'baseUrl'=>'http://' . $_SERVER['HTTP_HOST'],

			//web application name
			'name'=>'سیستم نظردهی',
			
			//set default time zone
			'defaultTimeZone'=>App::$config->defaultTimeZone,

			//Language selected
			'lang'=> App::$config->lang,

			//registered controllers
			'controller'=>array(
				'list'=>array('cm'),
				'default'=>'cm',
			),

			//set default pagelayouts
			'pageLayouts'=>'cmlayouts/column1',
	
			//Module name
			'moduleName'=>'comment',

			//set include paths
			'includes'=>array(
				get_include_path(),
				$_SERVER['DOCUMENT_ROOT'],
				dirname(__FILE__),
				dirname( dirname(__FILE__) ),
				'..',
			),

			//imports...
			'imports'=>array(
				'components',//system
                'extentions',
                'messages',

                'modules/comment/components',
				'modules/comment/controllers',
                'modules/comment/models/',
				'modules/comment/extentions',
				'modules/comment/messages',
//
                'modules/profile/models',

                //call precedence files
                'precedence'=>array(
                    'components'=>array(
                        'CController',
                        'Form',
                        'database',
                    ),
                ),
			),
			
			//connect to database
			'db'=>array(
				'stringConnection'=>App::$config->db->stringConnection,
				'username'=>App::$config->db->username,
				'password'=>App::$config->db->password,
				//ping mode
				'ping'=>App::$config->db->ping,
				
				//cache mode
				'cache'=>array(
                        'enable'=>App::$config->db->cache->enable,
						/*
							0 or OFF caching is off
							1 or ON caching without use SQL_CACHE in SELECT query
							2 caching only whene you use SQL_CACHE in query
						*/
						'type'=>App::$config->db->cache->type, //query_cache_type
						'size'=>App::$config->db->cache->size, //query_cahce_size
						'var_scope'=>App::$config->db->cache->var_scope, //It can be global or session
						'wlock_invalidate'=>App::$config->db->cache->wlock_invalidate,
				),
			),	


			//email account
			'email'=>array(
				'host'=>App::$config->email->host,
				'username'=>App::$config->email->username,
				//'password'=>App::$config->email->password,
				'port'=>App::$config->email->port,
			),

			//set default language alignment
			'langAlignment'=>array(
				'dir'=>'rtl',
				'align'=>'right'
			),		

			//cookie
			'cookie'=>array(
				'expire'=>App::$config->cookie->expire,
				'path'=>App::$config->cookie->path,
			),
			
			//session
			'session'=>array(
				'autoStart'=>App::$config->session->autoStart,
			),


			//Twig template engine
			'Twig'=>array(
				'fileSystem'=>dirname(__FILE__) . '/../views/',
				'cache'=>array(
					'folder'=>dirname(__FILE__) . '/../templates/',
					'autoReload'=>true
				)
			),
			
			//error driven
			'errorDriven'=>array(
				'reporting'=>App::$config->errorDriven->reporting,
				'display'=>App::$config->errorDriven->display,
				'logFile'=>App::$config->errorDriven->logFile,
			),
);