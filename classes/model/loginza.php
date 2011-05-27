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
    
    public static function signup($data)
    {
        $data['is_loginza'] = TRUE;
        
        $loginza_data = Session::instance()->get('loginza_data');

        if ( ! isset($loginza_data['profile']['email']) OR $data['email'] != $loginza_data['profile']['email'])
        {
           $data['active'] = FALSE;
        }
        else
        {
            $data['active'] = TRUE;
        }

        $member = Jelly::factory($this->module_name, $data)->save();

        if ($member->saved())
        {
            Session::instance()->delete('loginza_data');

            Model_Loginza::add_provider($member->id, $loginza_data['system']);

            if ( ! $_POST['active'])
            {
                self::emailactivate($member->id, $member->email);
            }
        }
        
        return $member;
    }

}
  