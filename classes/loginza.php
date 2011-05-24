<?php defined('SYSPATH') or die('No direct script access.');

class Loginza
{
    protected $api_url = 'http://loginza.ru/api/%method%';
    protected $version = '1.0';
    protected $widget_url = 'https://loginza.ru/api/widget';

    public static function factory()
    {
        return new self();
    }
    
    public function __construct()
    {
        
    }
    
    public function check()
    {
        if ( ! empty($_POST['token'])) 
        {
            $profile = $this->_get_auth_info($_POST['token']);
            
            if ( ! empty($profile['error_type'])) 
            {
                // есть ошибки, выводим их
                // в рабочем примере данные ошибки не следует выводить пользователю, так как они несут информационный характер только для разработчика
                echo $profile['error_type'].": ".$profile['error_message'];
                
                return FALSE;
            }
            elseif (empty($profile)) 
            {
                // прочие ошибки
                echo 'Temporary error.';
                
                return FALSE;
            }
            else 
            {    
                $profile = $this->_parse_profile($profile);
            
                return $profile;
            }
        }
        
        return FALSE;
    }
    
    protected function _parse_profile($profile)
    {
        $array = array(
            'nickname' => Arr::get($profile, 'nickname', $this->_gen_nickname($profile)),
            'site' => Arr::get($profile, 'site', $this->_gen_site($profile)),
            'full_name' => Arr::get($profile, 'full_name', $this->_gen_full_name($profile)),
        );
        
        $profile = Arr::merge($profile, $array);
        
        return $profile;
    }
    
    protected function _gen_site($profile)
    {
		if (isset($profile['web']['blog']) AND ! empty($profile['web']['blog'])) 
        {
			return $profile['web']['blog'];
		}
        elseif (isset($profile['web']['default']) AND ! empty($profile['web']['default'])) 
        {
			return $profile['web']['default'];
		}
		
		return FALSE;
	}

    protected function _gen_nickname($profile)
    {
        if (isset($profile['email']) AND ! empty($profile['email']) AND preg_match('/^(.+)\@/i', $profile['email'], $nickname)) 
        {
			return $nickname[1];
		}
        
		// шаблоны по которым выцепляем ник из identity
		$patterns = array(
			'([^\.]+)\.ya\.ru',
			'openid\.mail\.ru\/[^\/]+\/([^\/?]+)',
			'openid\.yandex\.ru\/([^\/?]+)',
			'([^\.]+)\.myopenid\.com'
		);
        
		foreach ($patterns as $pattern) 
        {
			if (preg_match('/^https?\:\/\/'.$pattern.'/i', $profile['identity'], $result)) 
            {
				return $result[1];
			}
		}
		
		return FALSE;
    }
    
    protected function _gen_full_name($profile) 
    {
		if (isset($profile['name']['full_name'])) 
        {
			return $profile['name']['full_name'];
		}
        
        $full_name = trim(
                     (isset($profile['name']['first_name']) ? $profile['name']['first_name'] : '') .' '. 
                     (isset($profile['name']['last_name']) ? $profile['name']['last_name'] : '')
                     );
        
        if ($full_name)
        {
            return $full_name;
        }
		
		return FALSE;
	}

    protected function _get_auth_info($token)
    {
        return $this->_api_request('authinfo', array('token' => $token));
    }
    
    protected function _api_request($method, $params) {
		// url запрос
		$url = str_replace('%method%', $method, $this->api_url).'?'.http_build_query($params);
		
		if ( function_exists('curl_init') ) 
        {
			$curl = curl_init($url);
			$user_agent = 'LoginzaAPI'.$this->version.'/php'.phpversion();
			
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_HEADER, FALSE);
			curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			$raw_data = curl_exec($curl);
			curl_close($curl);
			$responce = $raw_data;
		} 
        else 
        {
			$responce = file_get_contents($url);
		}
		
		// обработка JSON ответа API
		return json_decode($responce, TRUE);
	}
    
    public function get_widget_url($return_url = NULL, $provider = NULL, $overlay='') 
    {
		$params = array();
		
		if ( ! $return_url) 
        {
			$params['token_url'] = $this->_current_url();
		}
        else 
        {
			$params['token_url'] = $return_url;
		}
		
		if ($provider) 
        {
			$params['provider'] = $provider;
		}
		
		if ($overlay) 
        {
			$params['overlay'] = $overlay;
		}
		
		return $this->widget_url.'?'.http_build_query($params);
	}
    
    private function _current_url() 
    {
		$url = array();
		// проверка https
		if(isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS']=='on') 
        {
			$url['sheme'] = "https";
			$url['port'] = '443';
		} 
        else 
        {
			$url['sheme'] = 'http';
			$url['port'] = '80';
		}
		// хост
		$url['host'] = $_SERVER['HTTP_HOST'];
		// если не стандартный порт
		if (strpos($url['host'], ':') === FALSE AND $_SERVER['SERVER_PORT'] != $url['port']) 
        {
			$url['host'] .= ':'.$_SERVER['SERVER_PORT'];
		}
        
		// строка запроса
		if (isset($_SERVER['REQUEST_URI'])) 
        {
			$url['request'] = $_SERVER['REQUEST_URI'];
		} 
        else
        {
			$url['request'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
			$query = $_SERVER['QUERY_STRING'];
			if (isset($query)) 
            {
			  $url['request'] .= '?'.$query;
			}
		}
		
		return $url['sheme'].'://'.$url['host'].$url['request'];
	}
}