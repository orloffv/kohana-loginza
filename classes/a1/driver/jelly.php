<?php defined('SYSPATH') or die('No direct script access.');

abstract class A1_Driver_Jelly extends A1 {

	protected function _load_user($username)
    {
        return parent::_load_user($username);
    }

    protected function _load_user_loginza($provider, $identity)
	{
        $query = Jelly::select($this->_config['user_model'])
                ->join('loginza')->on('loginza.'.$this->_config['user_model'].'_id', '=', $this->_config['user_model'].'_id')
                ->where('loginza.provider', '=', $provider)->where('loginza.identity', '=', $identity);

        if (isset($this->_config['columns']['active']))
        {
            $query = $query->where($this->_config['columns']['active'], '=', TRUE);
        }

        $query = $query->limit(1)->execute();
        
		return $query;
	}
    
    public function login_loginza($provider, $identity, $remember = FALSE)
    {
        if (empty($provider) OR empty($identity))
        {
            return FALSE;
        }
        
        $user = $this->_load_user_loginza($provider, $identity);
        
        if ( $user->loaded())
		{
            return $this->complete_login($user,$remember);
		}
    }
}