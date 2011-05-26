<?php
abstract class A1 extends A1_Core {
    
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