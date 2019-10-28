<?php

return [
		'app' => [
				/*
				 |--------------------------------------------------------------------------
				 | Epesi name
				 |--------------------------------------------------------------------------
				 |
				 | This value is the name of Epesi as displayed.
				 |
				 */
				'title' => 'EPESI',
				
				/*
				 |--------------------------------------------------------------------------
				 | Epesi skin
				 |--------------------------------------------------------------------------
				 |
				 | This default skin.
				 |
				 */
				'skin' => 'semantic-ui',
				
				'cdn' => [
							'atk'              	=> 'https://cdn.jsdelivr.net/gh/atk4/ui@1.7.1/public',
							'jquery'           	=> 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1',
							'serialize-object' 	=> 'https://cdnjs.cloudflare.com/ajax/libs/jquery-serialize-object/2.5.0',
							'semantic-ui'      	=> 'https://cdn.jsdelivr.net/npm/fomantic-ui@2.7.2/dist',
							'draggable'      	=> 'https://cdn.jsdelivr.net/npm/@shopify/draggable@1.0.0-beta.5/lib',
							'jquery-ui'      	=> 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.0',
				],
		],
		'joints' => [

		],
		'disk' => [
				'driver' => 'local',
				'root' => storage_path('epesi')
		]
];
