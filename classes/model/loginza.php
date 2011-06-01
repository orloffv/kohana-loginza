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
                        array(array(':model', 'check_uniq_identity'), array(':model')),
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
    
    public static function check_uniq_identity(Jelly_Model $model)
    {
        $identity = $model->_changed['identity'];
        $provider = $model->_changed['provider'];
        
        $count = Jelly::query('loginza')->where('identity', '=', $identity)->where('provider', '=', $provider)->execute()->count();
        
        return ($count) ? FALSE : TRUE;
    }
    
    public static function add_provider($member_id, $system)
    {
        $insert = Arr::merge($system, array('member_id' => $member_id));
        
        try
        {
            return Jelly::factory('loginza')->set($insert)->save();
        }
        catch (Jelly_Validation_Exception $e)
        {
            return $e->errors();
        }
        catch (Database_Exception $e)
        {
            return FALSE;
        }
    }
    
    public static function delete_provider($provider_id)
    {
        try
        {
            $member = A1::instance()->get_user();
            $member_model = Jelly::query('member', $member->id)->execute();
            $providers = Jelly::query('loginza')->where('member_id', '=', $member->id)->execute()->count();
            if ($providers > 1 OR $member_model->password)
            {
                return Jelly::query('loginza', $provider_id)->delete();
            }
            else
            {
                return array('provider' => 'Нужен хотябы один метод авторизации');
            }
        }
        catch (Jelly_Validation_Exception $e)
        {
            return $e->errors();
        }
        catch (Database_Exception $e)
        {
            return FALSE;
        }
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

            Model_Loginza::signin($loginza_data['system']);
            
            if ( ! $data['active'])
            {
                self::emailactivate($member->id, $member->email);
            }
        }
        
        return $member;
    }

}
  