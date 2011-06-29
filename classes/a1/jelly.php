<?php defined('SYSPATH') or die('No direct script access.'); 

class A1_Jelly extends A1_Driver_Jelly {
    
    protected function _load_user($username)
    {
        $query = Jelly::query($this->_config['user_model'])
			->where($this->_config['columns']['username'], '=', $username);

		if (isset($this->_config['columns']['active']))
		{
			$query = $query->where($this->_config['columns']['active'], '=', TRUE);
		}

		return $query->limit(1)->execute();
    }

    protected function _load_user_loginza($provider, $identity)
	{
        $query = Jelly::query($this->_config['user_model'])
                ->join('loginza')->on('loginza.'.$this->_config['user_model'].'_id', '=', $this->_config['user_model'].'.id')
                ->where('loginza.provider', '=', $provider)->where('loginza.identity', '=', $identity);

        if (isset($this->_config['columns']['active']))
        {
            $query = $query->where($this->_config['columns']['active'], '=', TRUE);
        }

        return $query->limit(1)->execute();
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
    
    public function update_user($user)
    {
        $new_user = $this->_load_user($user->email);
        $this->complete_login($new_user, TRUE);
    }
    
    public function check($password, $hash)
	{
        if ( ! $hash)
        {
            return FALSE;
        }
        
        return parent::check($password, $hash);
    }
    
}
