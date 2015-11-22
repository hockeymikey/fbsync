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
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\App;
use \OCA\FbSync\App\Contacts;
use \OCA\FbSync\Controller\FacebookController;


class PageController extends Controller {

	private $app;

	public function __construct($AppName, IRequest $request, Contacts $contacts, FacebookController $facebook){
		parent::__construct($AppName, $request);
		$this->contacts = $contacts;
		$this->facebook = $facebook;
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
     */
    

    /**
	 * @NoCSRFRequired
	 */
	public function index() {
		$params = [];
		return new TemplateResponse('fbsync', 'status', $params);  // templates/status.php
	}

	/**
	 * @NoCSRFRequired
	 */
	public function match() {
		$params = ['contacts' => $this->contacts, 'facebook' => $this->facebook];
		if(!$this->facebook->islogged()) {
			return new TemplateResponse('fbsync', 'login', $params);  // templates/login.php
		}
		return new TemplateResponse('fbsync', 'match', $params);  // templates/match.php
	}
    
    /**
	 * @NoCSRFRequired
	 */
	public function sync() {
		$params = ['contacts' => $this->contacts, 'facebook' => $this->facebook];
		if(!$this->facebook->islogged()) {
			return new TemplateResponse('fbsync', 'login', $params);  // templates/login.php
		}
		return new TemplateResponse('fbsync', 'sync', $params);  // templates/sync.php
	}

}