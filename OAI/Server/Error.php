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
	
	$Id: Error.php,v 1.2 2003/04/22 08:59:33 ordnas Exp $
*/

require_once 'OAI/Base.php';

/**
 * OAI_ServerError
 * Error handling of OAI server as specified for OAI and HTTP protocol.
 *
 * @access   public
 * @version  $Id: Error.php,v 1.2 2003/04/22 08:59:33 ordnas Exp $
 * @package  OAI
 * @author   Sandro Zic <ordnas@php.net> Rewrite for PEAR
 * @author   Heinrich Stamerjohanns <stamer@uni-oldenburg.de> Original Author
 */
class OAI_ServerError
{

  /**
   * Error messages returned to requesting party
   *
   * @var  string
   * @see  addMessage(), getMessage()
   */
  var $error_code = '';

  /**
   * Error messages returned to requesting party
   *
   * @var  string
   * @see  addMessage(), getMessage()
   */
  var $error_description = '';

  /**
   * message
   *
   * returns a SOAP_Message class that can be sent as a server response
   *
   * @access public
   */
  function addMessage($code, $argument = '', $value = '')
  {
    switch ($code) {
    case 'badArgument' :
      $text
        = "The request includes illegal arguments, is missing required arguments, " .
        "includes a repeated argument, or values for arguments have an illegal syntax.";
      break;

    case 'badGranularity' :
      $text = "The value '$value' of the argument '$argument' is not valid.";
      $code = 'badArgument';
      break;

    case 'badGranularityCombination':
      $text = 'The values ' . join(' and ', $value) . ' do not have the same granularity.';
      $code = 'badArgument';
      break;

    case 'badResumptionToken' :
      $text = "The value of the resumptionToken argument is invalid or expired.";
      break;

    case 'badRequestMethod' :
      $text = "The request method '$argument' is unknown.";
      $code = 'badVerb';
      break;

    case 'badVerb' :
      $text = "Value of the verb argument is not a legal OAI-PMH verb, the verb argument " .
        "is missing, or the verb argument is repeated.";
      break;

    case 'cannotDisseminateFormat' :
      $text = "The metadata format '" . $argument . "' identified by the value given for the metadataPrefix " .
        "argument is not supported by the item or by the repository.";
      break;

      // not OAI standard
    case 'exclusiveArgument' :
      $text = 'The usage of resumptionToken as an argument allows no other arguments.';
      $code = 'badArgument';
      break;

    case 'idDoesNotExist' :
      $text = "The value of the identifier argument is unknown or illegal in this repository.";
      break;

      // not OAI standard
    case 'missingArgument' :
      $text = "The required argument '$argument' is missing in the request.";
      $code = 'badArgument';
      break;

      // not OAI standard
    case 'noRecordsMatch' :
      $text = 'The combination of the given values results in an empty list.';
      break;

    case 'noMetadataFormats' :
      $text = 'There are no metadata formats available for the specified item.';
      break;

      // not OAI standard
    case 'noVerb' :
      $text = 'The request does not provide any verb.';
      $code = 'badVerb';
      break;

    case 'noSetHierarchy' :
      $text = 'The repository does not support sets.';
      break;

    default:
      $text = "Unknown error: code: '$code', argument: '$argument', value: '$value'";
      $code = 'badArgument';
    }

    $this->error_code = OAI_Base::xmlEncode($code);
    $this->error_description = OAI_Base::xmlEncode($text);
  }

  /**
   * Check if errors occured
   *
   * @return boolean
   * @access public
   */
  function isError()
  {
    if (!strlen($this->error_code)) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * getCode
   *
   * returns a string with the error code
   *
   * @return string
   * @access public
   */
  function getCode()
  {
    return $this->error_code;
  }

  /**
   * Get error description
   *
   * returns a string with the error description
   *
   * @return string
   * @access public
   */
  function getDescription()
  {
    return $this->error_description;
  }

  /**
   * Internal Server Error
   *
   * Returns a HTTP 500 error code and exits application.
   *
   * @param string $msg The error message for the administrator.
   *
   * @access public
   */
  function internalServerError($msg)
  {
    // issue HTTP "Internal Server Error" header
    include_once 'HTTP/Header.php';
    HTTP_Header::sendStatusCode(500);
    PEAR::raiseError($msg);
    exit;
  }
}

?>