<?php defined('SYSPATH') or die('No direct script access.');

class Model_Loginza extends Jelly_Model {

    public static function initialize(Jelly_Meta $meta)
    {
        $meta->table('loginza')
            ->fields(array(
                'id' => Jelly::field('primary'),
                'identity' => Jelly::field('string', array(
                    'label' => __('Identity'),
                    'rules' => array(
                        array('not_empty'),
                    )
                )),
                'provider' => Jelly::field('string', array(
                    'label' => __('Provider'),
                    'rules' => array(
                        array('not_empty'),
                    )
                )),
                'dt_create' => Jelly::field('my_dtcreate'),
                'member_id' => Jelly::field('integer'),
            ));
    }
    
    public static function add_provider($member_id, $system)
    {
        $insert = Arr::merge($system, array('member_id' => $member_id));
        
        return Jelly::factory('loginza')->set($insert)->save();
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

        $member = Jelly::factory('member')->set($data)->save();

        if ($member->saved())
        {
            Session::instance()->delete('loginza_data');

            Model_Loginza::add_provider($member->id, $loginza_data['system']);

            if ( ! $data['active'])
            {
                self::emailactivate($member->id, $member->email);
            }
        }
        
        return $member;
    }

}
  