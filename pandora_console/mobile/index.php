<?php
// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2010 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation for version 2.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

//Set character encoding to UTF-8 - fixes a lot of multibyte character
//headaches
if (function_exists ('mb_internal_encoding')) {
	mb_internal_encoding ("UTF-8");
}

$develop_bypass = 1;

require_once("include/ui.class.php");
require_once("include/system.class.php");
require_once("include/db.class.php");
require_once("include/user.class.php");

require_once('operation/home.php');
require_once('operation/tactical.php');
require_once('operation/groups.php');
require_once('operation/events.php');
$enterpriseHook = enterprise_include('mobile/include/enterprise.class.php');

$system = System::getInstance();

$user = $system->getSession('user', null);
if ($user == null) {
	$user = User::getInstance();
}
else {
	$user->hackInjectConfig();
}

$action = $system->getRequest('action');
if (!$user->isLogged()) {
	$action = 'login';
}

switch ($action) {
	case 'ajax':
		$parameter1 = $system->getRequest('parameter1', false);
		$parameter2 = $system->getRequest('parameter2', false);
		
		switch ($parameter1) {
			case 'events':
				$events = new Events();
				$events->ajax($parameter2);
				break;
		}
		return;
		break;
	case 'login':
		if (!$user->checkLogin()) {
			$user->showLogin();
		}
		else {
			if ($user->isLogged()) {
				$home = new Home();
				$home->show();
			}
			else {
				$user->showLoginFail();
			}
		}
		break;
	case 'logout':
		$user->logout();
		$user->showLogin();
		break;
	default:
		$page = $system->getRequest('page', 'home');
		switch ($page) {
			case 'home':
			default:
				$home = new Home();
				$home->show();
				break;
			case 'tactical':
				$tactical = new Tactical();
				$tactical->show();
				break;
			case 'groups':
				$groups = new Groups();
				$groups->show();
				break;
			case 'events':
				$events = new Events();
				$events->show();
				break;
		}
		break;
}
?>
