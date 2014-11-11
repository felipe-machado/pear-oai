<?php

// if ($_SERVER['REMOTE_ADDR'] != '10.98.32.133') {
//     echo '<h1>Offline, server maintainance / serveronderhoud</h1>';
//     die;
// }
/*
	PEAR::OAI
	
	A PHP Implementation of the Open Archives Initiative Protocol 
	for Metadata Harvesting (http://www.openarchives.org)
	
    Copyright (C) 2003 ZZ/OSS GbR, http://www.zzoss.com

    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with this library; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	
	Author: Sandro Zic <ordnas@php.net>

	Adapted for CMDI and continued by Jan Pieter Kunst <janpieter.kunst@meertens.knaw.nl>
	
	Based on work by Heinrich Stamerjohanns <stamer@uni-oldenburg.de>
	
	$Id: oai_server.php,v 1.1.1.1 2003/06/30 11:50:06 ordnas Exp $
*/

// include the file with the general PHP environment
// configuration
// please check this file and adjust the parameters
// to your needs

require_once 'config/phpconfig.php';

// include the OAI Server (= Repository)
require_once 'OAI/Server.php';

// set the options for the OAI Server
//
// you can set global options in options.php or define
// them at runtime for a single instance of OAI::Server
//
// please have a look at options.php for available parameters

// instantiate OAI server
$oai_server =& new OAI_Server($options);

require_once 'config/storageoptions.php';
// include the server backend class
require_once 'OAI/Server/Backend/Cmdi/Backend.php';
// include the local output class
// require_once './Output.php';

// instantiate the server backend
$backend = new OAI_ServerBackendCmdi($storage_options);

// set storage driver
// 'DB'								use PEAR::DB
// 'mysql://root:@localhost/oai'	connect to MySQL database called 'oai' with
//									root user, no password
$backend->connect(OAI_STORAGE_DSN);
// OAI table is latin1
$backend->db->query("SET CHARACTER SET 'latin1'");

$oai_server->backend($backend);
// set storage driver for token if you want to use database to store
// resumption token session data
$oai_server->tokenStorage('DB', OAI_TOKENSTORAGE_DSN);

// display the response to the OAI request
$oai_server->response();

