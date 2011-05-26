<?php defined('SYSPATH') or die('No direct script access.');

abstract class A1_Driver_Jelly extends A1 {
    
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
}