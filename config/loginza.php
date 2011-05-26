<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
    'api_url' => 'http://loginza.ru/api/%method%',
    'version' => '1.0',
    'widget_url' => 'https://loginza.ru/api/widget',
    'widget_id' => '',
    'widget_key' => '',
    'mapping_paths' => array(
        'email' => 'email', 
        'firstname' => 'name.first_name', 
        'lastname' => 'name.last_name', 
        'nickname' => 'nickname',
        'd_birthday' => 'dob',  
    ),
);
