<?php defined('SYSPATH') or die('No direct script access.');

class Model_Loginza extends Jelly_Model {

    public static function initialize(Jelly_Meta $meta)
    {
        $meta->table('loginza')
            ->fields(array(
                'id' => new Field_Primary,
                'identity' => new Field_String(array(
                    'label' => __('Identity'),
                    'rules' => array(
                        'not_empty' => NULL,
                    )
                )),
                'provider' => new Field_String(array(
                    'label' => __('Provider'),
                    'rules' => array(
                        'not_empty' => NULL,
                    )
                )),
                'dt_create' => new Field_Timestamp(array(
                    'auto_now_create' => TRUE,
                    'format' => 'Y-m-d H:i:s',
                )),
                'member_id' => new Field_Integer,
            ));
    }
    
    public static function add_provider($member_id, $system)
    {
        $insert = Arr::merge($system, array('member_id' => $member_id));
        
        Jelly::factory('loginza', $insert)->save();
    }
    
    public static function signin($system, $remember = FALSE)
    {
        return ! A1::instance()->login_loginza($system['provider'], $system['identity'], $remember) === FALSE;
    }

}
  