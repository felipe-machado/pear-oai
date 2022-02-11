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
	
	$Id: Base.php,v 1.7 2003/06/30 11:38:15 ordnas Exp $
*/

// error debugging
//error_reporting(E_ALL);

// include PEAR base class
require_once 'PEAR.php';

/**
 * Do not change the constant values below, unless you know what you do,
 * e.g. for development purposes.
 *
 * User-defined configuration parameters for PEAR::OAI can be set in the
 * config.php file of the PEAR::OAI package, along the inline
 * documentation available within.
 */

/**
 * The OAI package version
 *
 * @const string OAI_PACKAGE_VERSION
 * @see   getPackageVersion()
 */
define('OAI_PACKAGE_VERSION', '0.4');
/**
 * The xml default encoding
 *
 * @const string OAI_RESPONSE_DEFAULT_ENCODING
 */
define('OAI_RESPONSE_DEFAULT_ENCODING', 'ISO-8859-1');
/**
 * The OAI protocol version
 *
 * Please consult for more information:
 * http://www.openarchives.org/
 *
 * @const string OAI_RESPONSE_DEFAULT_ENCODING
 */
define('OAI_PROTOCOL_VERSION', '2.0');
/**
 * The OAI namespace
 *
 * @const string OAI_RESPONSE_OAI_NAMESPACE
 */
define('OAI_RESPONSE_OAI_NAMESPACE', 'http://www.openarchives.org/OAI/2.0/');
/**
 * The xml schema namespace
 *
 * @const string OAI_RESPONSE_XSD_INSTANCE
 */
define('OAI_RESPONSE_XSD_INSTANCE', 'http://www.w3.org/2001/XMLSchema-instance');
/**
 * The OAI schema location
 *
 * @const string OAI_RESPONSE_OAI_SCHEMA_LOCATION
 */
define('OAI_RESPONSE_OAI_SCHEMA_LOCATION', 'http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
/**
 * The response content type.
 *
 * ... is usually text/xml, but can be set to text/plain for testing purposes
 * For example when in debug mode.
 *
 * @const string
 */
define('OAI_RESPONSE_CONTENT_TYPE', 'text/xml');

/**
 * OAI_Base
 * Common base class of all OAI classes
 *
 * @access   public
 * @version  $Id: Base.php,v 1.7 2003/06/30 11:38:15 ordnas Exp $
 * @package  OAI
 * @author   Sandro Zic <ordnas@php.net> Rewrite for PEAR
 * @author   Heinrich Stamerjohanns <stamer@uni-oldenburg.de> Original Author
 */
class OAI_Base extends PEAR
{

  /**
   * Define valid OAI verbs
   *
   * Please consult for more information:
   * http://www.openarchives.org/OAI/openarchivesprotocol.html
   *
   * @var array
   */
  var $requestAllowedVerbs
    = array(
      'GetRecord', 'Identify', 'ListIdentifiers', 'ListMetadataFormats', 'ListRecords', 'ListSets'
    );

  /**
   * Define which response for which verb will _not_ be compressed
   *
   * @var array
   */
  var $noCompression = array('Identify');

  /**
   * Configuration parameters
   *
   * @var array
   */
  var $options = array();

  /**
   * Debugging information.
   *
   * @var string
   */
  var $debugInfo;

  /** @var OAI_ServerError */
  var $_error;

  /**
   * @var array
   */
  var $args;

  /**
   * Constructor
   *
   * @param  $faultcode string  error code
   *
   * @see  $debug_data, _debug()
   */
  function OAI_Base($faultcode = 'Server')
  {
  }


  /**
   * Get the release number of this package.
   *
   * @access public
   * @return string
   */
  function getPackageVersion()
  {
    return OAI_PACKAGE_VERSION;
  }


  function tokenStorage($driver, $dsn, $options = array())
  {
    // assign the container type
    $this->options['token_container'] = $driver;
    // add the storage DSN to the OAI options
    $this->options['token_dsn'] = $dsn;
    // assign the table name
    if (isset($options['table'])) {
      $this->options['token_table'] = $options['table'];
    }
  }

  /**
   * Return a storage driver based on $driver and $options
   *
   * @access private
   * @static
   *
   * @param string $container The type of container
   * @param  string $driver   Type of storage class to return
   * @param  string $options  Optional parameters for the storage class
   *
   * @return object Object   Storage object
   */
  function &_factory($driver, $options = null)
  {
    $storage_path = "OAI/Server/Container/" . $driver . ".php";
    $storage_class = "OAI_ServerContainer" . $driver;

    require_once $storage_path;

    // instantiate driver
    $driver = new $storage_class($options);
    return $driver;
  }


  function _parseOptions($options_runtime = null)
  {
    /** @var $options */
    include_once 'config/options.php';
    $this->options = $options;

    // overwrite static options from options.php
    // with options assigned to specific instance
    if (is_array($options_runtime) && count($options_runtime)) {
      foreach ($options_runtime as $name => $value) {
        $this->options[$name] = $value;
      }
    }

    // check if we have to automatically assign some parameter values

    // assign base URL if empty
    if (!strlen($this->options['base_url'])) {
      $this->options['base_url'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
    }

    // compose the PEAR::OAI root path where the package is
    // installed
    if (!strlen($this->options['templates_path'])) {
      $this->options['templates_path'] = dirname(__FILE__) . '/Server/tpl/';
    }

    // check if we are in debug mode
    if ($this->options['debug']) {
      define('OAI_DEBUG', true);
      OAI_Base::debug('Set OAI_DEBUG constant to "true"', __FILE__, __LINE__);
    } else {
      define('OAI_DEBUG', false);
    }
  }

  function xmlEncode($string)
  {
    $invalid_chars = array(
      "\x16", "\x17", "\x18", "\x19", "\x1a", "\x1b",
      "\x1c", "\x1d", "\x1e", "\x1f", "\x2", "\x3", "\x4",
      "\x5", "\x0", "\xc", "\xe", "\xf", "\x10", "\x11",
      "\x12", "\x13", "\x14", "\x15", "\x1"
    );

    // just remove invalid characters
    $string = str_replace($invalid_chars, '', $string);

    $xmlstr = utf8_encode($string);
    if (OAI_DEBUG) {
      OAI_Base::debug('UTF-8 encoded "' . $string . '" to "' . $xmlstr . '"', __FILE__, __LINE__);
    }
    // must be done after utf8encode, so we do not escape the ampersand...
    $xmlstr = str_replace("'", '&apos;', $xmlstr);

    return $xmlstr;
  }

  function _isValid($name, $value)
  {
    switch ($name) {
    case 'verb':
      if (!isset($value)) {
        $this->_error->addMessage('noVerb');
        return false;
      } elseif (!in_array($value, $this->requestAllowedVerbs)) {
        $this->_error->addMessage('badVerb', $this->args['verb']);
        return false;
      }
      break;
    case 'identifier':
      if (!isset($value)) {
        $this->_error->addMessage('missingArgument', 'identifier');
        return false;
      }
      break;
    case 'metadataPrefix':
      if (!isset($value)) {
        $this->_error->addMessage('missingArgument', 'metadataPrefix');
        return false;
      }
      $formats = array_keys($this->options['metadata_formats']);
      if (!in_array($value, $formats)) {
        $this->_error->addMessage('cannotDisseminateFormat', $value);
        return false;
      }
      break;
    case 'resumptionToken':
      // resumptionToken MUST always be an exclusive argument,
      // only verb is allowed
      $args = $this->args;
      unset($args['verb']);
      if (is_array($args) && count($args) > 1) {
        $this->_error->addMessage('exclusiveArgument');
        return false;
      }
      break;
    case 'from':
      if (!$this->_checkDateFormat($value)) {
        // otherwise invalid attribute in error response
        unset($this->args['from']);
        $this->_error->addMessage('badGranularity', 'from', $value);
        return false;
      }
      break;
    case 'until':
      if (!$this->_checkDateFormat($value)) {
        // otherwise invalid attribute in error response
        unset($this->args['until']);
        $this->_error->addMessage('badGranularity', 'until', $value);
        return false;
      }
      break;
    case 'from_until':
      if (!$this->_checkSameGranularity($value)) {
        $this->_error->addMessage('badGranularityCombination', '', $value);
        return false;
      }
      break;
    default:
      PEAR::raiseError('No validation routine available for ' . $name);
      break;
    }

    return true;
  }

  function _checkDateFormat($date)
  {
    if ($this->options['granularity'] == 'YYYY-MM-DDThh:mm:ssZ') {
      $checkstr = '/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})T([0-9]{2}):([0-9]{2}):([0-9]{2})Z$/';
    } else {
      $checkstr = '/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}$)/';
    }
    if (preg_match($checkstr, $date, $regs)) {
      if (checkdate($regs[2], $regs[3], $regs[1])) {
        return true;
      } else {
        PEAR::raiseError("Invalid Date: $date is not a valid date.");
        return false;
      }
    } else {
      if ($this->options['granularity'] == 'YYYY-MM-DDThh:mm:ssZ') {
        if (preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}$)/', $date, $regs)) {
          if (checkdate($regs[2], $regs[3], $regs[1])) {
            return true;
          }
        }
      } else {
        PEAR::raiseError(
          "Invalid Date Format: $date does not comply to the date format " . $this->options['granularity']);
        return false;
      }
    }
  }

  function _checkSameGranularity($dates) {
    $type1 = '/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})T([0-9]{2}):([0-9]{2}):([0-9]{2})Z$/';
    $type2 =  '/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}$)/';
    if (preg_match($type1, $dates[0]) && preg_match($type1, $dates[1])) {
      return true;
    } elseif (preg_match($type2, $dates[0]) && preg_match($type2, $dates[1])) {
      return true;
    } else {
      PEAR::raiseError("Invalid granularity: {$dates[0]} and {$dates[1]} should have the same granularity");
      return false;
    }
  }

  function _formatDatestamp($datestamp)
  {
    return $this->_date2UTCdatestamp($datestamp);
  }

  function _date2UTCdatestamp($date)
  {
    switch ($this->options['granularity']) {

    case 'YYYY-MM-DDThh:mm:ssZ':
      // we assume common date ("YYYY-MM-DD") or
      // datetime format ("YYYY-MM-DD hh:mm:ss")
      // in the database
      if (strstr($date, ' ')) {
        // date is datetime format
        return str_replace(' ', 'T', $date) . 'Z';
      } else {
        // date is date format
        // granularity 'YYYY-MM-DD' should be used...
        return $date . 'T00:00:00Z';
      }
      break;

    case 'YYYY-MM-DD':
      if (strstr($date, ' ')) {
        // date is datetime format
        $tmp = explode(" ", $date);
        $date = $tmp[0];
        return $date;
      } else {
        return $date;
      }
      break;

    default:
      PEAR::raiseError("Unknown granularity!");
      break;
    }
  }

  function debug($msg, $file, $line)
  {
    error_log('OAI debug: ' . $msg . ' in ' . $file . ' on line ' . $line);
  }
}    
