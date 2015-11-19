<?php
/**
 * ownCloud - fbsync
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author NOIJN <fremulon@protonmail.com>
 * @copyright NOIJN 2015
 */

namespace OCA\FbSync\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\IUser;
use \OCP\ICache;
use OCA\FbSync\AppInfo\Application as App;
require("simple_html_dom.php");


class FacebookController extends Controller {
		
	/**
	 * @var ICache
	 */
	private $cache;
	
	/**
	 * @var String Cookie location
	 * @var String useragent for requests (fake desktop)
	 */
	private $userHome;
	private $cookieName = '/fbsync.cookie';
	private $userAgent = '"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"';
	/**
	 * @var Int Friends cache time (24h)
	 * @var Int Friends cache key
	 */
	private $cacheTime = 24*60*60;
	private $cacheKey = "FBfriends";

	public function __construct($AppName, IRequest $request, ICache $cache, $userHome){
		parent::__construct($AppName, $request);
		$this->cache = $cache;
		$this->userHome = $userHome;
	}
    
    /**
     * Use and save cookie to do stuff on facebook.
     * Handle the login request.
     */
    private function dorequest($url, $post=false) {

        $ch = curl_init();
		
		// Create cookie file to prevent write error
		$cookie = fopen($this->userHome.$this->cookieName, "a+") or die("Unable to open file!");
		fclose($cookie);
		
        if(is_array($post)) {
            $curlConfig = array(
                CURLOPT_URL             => $url,
                CURLOPT_POST            => 1,
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_COOKIEFILE      => $this->userHome.$this->cookieName,
                CURLOPT_COOKIEJAR       => $this->userHome.$this->cookieName,
                CURLOPT_USERAGENT       => $this->userAgent,
                CURLOPT_FOLLOWLOCATION  => 1,
                CURLOPT_REFERER         => $url,
                CURLOPT_POSTFIELDS      => $post
            );
        } else {
            $curlConfig = array(
                CURLOPT_URL             => $url,
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_COOKIEFILE      => $this->userHome.$this->cookieName,
                CURLOPT_USERAGENT       => $this->userAgent,
                CURLOPT_FOLLOWLOCATION  => 1,
                CURLOPT_REFERER         => $url
            );
        }
        
        curl_setopt_array($ch, $curlConfig);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
		
        return array($info, $result);
    }

    /**
     * Try to log into Facebook and return results and header (for debug)
     */
    private function fblogin($user, $pass) {
        // Submit those variables to the server
        $post_data = array(
            'email' => $user,
            'pass' => $pass,
            'default_persistent' => 1,
            'login' => 'Connexion',
            'version' => 1,
            'lsd' => 'AVqBNk4B',
            'ajax' => '0',
            'm_ts' => time()
        );

        $html = $this->dorequest('https://m.facebook.com/login.php', $post_data);

    	//echo ($html[1]);
    	//die();

        if(preg_match('/home/i', $html[0]['url'])) {
            $Status = "success";
        } else if(preg_match('/checkpoint/i', $html[0]['url'])) {
            $Status = "checkpoint";
        } else if(preg_match('/login\.php$/i', $html[0]['url'])) {
			// already logged in
            $Status = "success";
        } else if(preg_match('/login/i', $html[0]['url'])) {
            $Status = "password";
        } else {
            $Status = "error";
        }

        return array($Status, json_encode($html[0]));

    }

    /**
     * Try to log into Facebook and return results and header (for debug)
	 * @var bool Ignore cache and force reload
     */
    public function getfriends($ignoreCache=false) {
		// try cache
		$cachedFriends = json_decode($this->cache->get($this->cacheKey), true);
		if(!empty($cachedFriends) && is_array($cachedFriends) && !$ignoreCache) {
			return $cachedFriends;
			
		} else if($this->is_logged()) {
			$this->cache->remove($this->cacheKey);

			$friends = array();
			$page=0;
			$friendLinkFilter = "[href^=/friends/hovercard]";
			$url = 'https://m.facebook.com/friends/center/friends/?ppk=';

			$getdata = $this->dorequest($url.$page);
			$html = str_get_html($getdata[1]);
			if (empty($html)) {
				return 1;
			}
			$main = $html->find('div[id=friends_center_main]', 0);
			if (empty($main)) {
				return 2;
			}

			// 10 per page. Break when next page empty!
			while(count($main->find('a'.$friendLinkFilter)) != 0) {
				foreach($main->find('a'.$friendLinkFilter) as $friend) {
					// FB ID
					$re = "/uid=([0-9]{1,20})/";
					preg_match($re, $friend->href, $matches);
					// $friends[fbid]=name
					$friends[(int)$matches[1]]=$friend->innertext;
				}
				$page++;

				$getdata = $this->dorequest($url.$page);
				$html = str_get_html($getdata[1]);
				$main = $html->find('div[id=friends_center_main]', 0);
			}
			\OCP\Util::writeLog('fbsync', count($friends)." friends cached", \OCP\Util::INFO);
			// Alphabetical order
			asort($friends);
			// To cache
			$this->cache->set($this->cacheKey, json_encode($friends), $this->cacheTime);
			
			return $friends;
			
		} else {
			return false;
		}
    }
	

    /**
     * Get picture for user who disabled the graph api
	 * @var integer The Facebook ID
     */
	public function getPicture_alt($fbid) {
		$getdata = $this->dorequest("https://m.facebook.com/$fbid");
		if (empty($getdata[1])) {
			return false;
		}
		$re = "/(photo\\.php\\?fbid=[0-9]{0,20}&amp;id=[a-z0-9;&.=\\\\]{20,300})\\\"/mi"; 
		preg_match_all($re, $getdata[1], $matches);
		if(empty($matches)) {
			return false;
		}
		$getdata2 = $this->dorequest('https://m.facebook.com/'.$matches[1][0]);
		$re2 = "/<img src=\\\"(https:\\/\\/[\\\"&-_.\\/;=?a-z0-9\\\"]*hphotos[\\\"&-_.\\/;=?a-z0-9\\\"]*)\\\"/mi"; 
		preg_match_all($re2, html_entity_decode($getdata2[1]), $matches2);
		if(empty($matches2)) {
			return false;
		}
		return $matches2[1][0];
	}
	
	/**
	 * Check if logged to facebook
	 * @NoAdminRequired
	 */
	public function is_logged() {
		// No cookie set, we don't need to go further
		if(filesize($this->userHome.$this->cookieName) == 0) {
			return false;
		}
		// Check if redirected to the login page
		$testlogin = $this->dorequest("https://m.facebook.com/friends/");
		if(preg_match('/login/i', $testlogin[0]['redirect_url'])) {
            return false;
        } else {
            return true;
        }
	}

	/**
	 * Login to facebook
	 * @NoAdminRequired
	 */
	public function login($email, $pass) {
        return $this->fblogin(base64_decode($email), base64_decode($pass));
	}
	
	/**
	 * Force reload cache
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function reload() {
//		\OCP\Util::writeLog('fbsync', "Cron launched", \OCP\Util::INFO);
		$friends = $this->getfriends(true);
        return is_bool($friends) ? $friends : count($friends);
	}
	
	/**
	 * Get cache info
	 * @NoAdminRequired
	 */
	public function fromCache() {
		$cachedFriends = json_decode($this->cache->get($this->cacheKey), true);
		return (!empty($cachedFriends) && is_array($cachedFriends));
	}

}