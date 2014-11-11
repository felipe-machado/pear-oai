<?php
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
	
	Based on work by Heinrich Stamerjohanns <stamer@uni-oldenburg.de>
	
	$Id: phpconfig.php,v 1.1.1.1 2003/06/30 11:50:07 ordnas Exp $
*/

/*
* Here we deal with the include path of your PEAR classes which also
* contain the OAI package.
*
* If you have the include path properly set in php.ini, you do not
* have to provide it here, so comment the below lines.
*
* More information about the PHP include_path directive can be found at
* http://www.php.net/manual/en/configuration.directives.php#ini.include-path
*/

/*
$include_path_pear = '/usr/share/php';

// assign include path depending on our operating system.
if (substr(PHP_OS, 0, 3) == 'WIN') {
    // include_path for Windows
  	ini_set('include_path', '.;'.$include_path_pear);
} else {
    // include path for Linux
  	ini_set('include_path', '.:'.$include_path_pear);
}
*/

error_reporting(E_WARNING);

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR .  realpath(dirname(__FILE__)));

// include the PEAR base class
require_once 'PEAR.php';

// now we set the PEAR error handler (this has to be done
// after including the PEAR base class, of course)
// More information at
// http://pear.php.net/manual/en/class.pear-error.php
// ini_set('display_errors', 1);
/** @noinspection PhpDynamicAsStaticMethodCallInspection */
// PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'pear_error_handler');
PEAR::setErrorHandling(PEAR_ERROR_RETURN);

function pear_error_handler($error_object) {

  echo '<pre>';
  var_dump($error_object);
  echo '</pre>';
}
