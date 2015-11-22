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

return [
    'routes' => [
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#index', 'url' => '/status', 'verb' => 'GET'],
	   ['name' => 'page#match', 'url' => '/match', 'verb' => 'GET'],
	   ['name' => 'page#sync', 'url' => '/sync', 'verb' => 'GET'],
	   ['name' => 'facebook#login', 'url' => '/facebook/login', 'verb' => 'POST'],
	   ['name' => 'facebook#reload', 'url' => '/facebook/reloadfriends', 'verb' => 'GET'],
	   ['name' => 'facebook#islogged', 'url' => '/facebook/islogged', 'verb' => 'GET'],
	   ['name' => 'contacts#perfectMatch', 'url' => '/perfectmatch', 'verb' => 'GET'],
	   ['name' => 'contacts#approxMatch', 'url' => '/approxmatch', 'verb' => 'GET'],
	   ['name' => 'contacts#updateFBID', 'url' => '/contact/fbid/{id}', 'verb' => 'POST'],
	   ['name' => 'contacts#setPhoto', 'url' => '/setphoto/{id}', 'verb' => 'GET'],
	   ['name' => 'contacts#getFbContacts', 'url' => '/FBcontacts', 'verb' => 'GET'],
	   ['name' => 'contacts#getPhoto', 'url' => '/getphoto/{id}/{size}', 'verb' => 'GET'],
    ]
];
