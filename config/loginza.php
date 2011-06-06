<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
    'api_url' => 'http://loginza.ru/api/%method%',
    'version' => '1.0',
    'widget_url' => 'https://loginza.ru/api/widget',
    'widget_id' => '',
    'widget_key' => '',
    'user_model' => 'member',
    'mapping_paths' => array(
        'email' => 'email', 
        'firstname' => 'name.first_name', 
        'lastname' => 'name.last_name', 
        'nickname' => 'nickname',
        'd_birthday' => 'dob',  
    ),
    'detect_provides' => array(
        'google'    => 'https://www.google.com/accounts/o8/ud',
        'yandex'    => 'http://openid.yandex.ru/server/',
        'vkontakte' => 'http://vkontakte.ru/',
        'mail_ru'   => 'http://mail.ru/',
        'facebook'  => 'http://www.facebook.com/',
        'twitter'   => 'http://twitter.com/',
        'steam'     => 'https://steamcommunity.com/openid/login',
        'last_fm'   => 'http://www.last.fm/'
    ),
);
