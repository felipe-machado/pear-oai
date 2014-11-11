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
	
	$Id: Server.php,v 1.11 2003/07/09 15:27:11 ordnas Exp $
*/

// include OAI base class
require_once 'OAI/Base.php';
// IT template system
require_once "HTML/Template/IT.php";
// OAI output class
require_once 'OAI/Server/Output.php';

/**
* OAI_Server
* Class of OAI server which provides metadata about a OAI repository.
*
* @access   public
* @version  $Id: Server.php,v 1.11 2003/07/09 15:27:11 ordnas Exp $
* @package  OAI
* @author   Sandro Zic <ordnas@php.net> Rewrite for PEAR
* @author   Heinrich Stamerjohanns <stamer@uni-oldenburg.de> Original Author
*/
class OAI_Server extends OAI_Base
{
	
	/**
	* The OAI Server Error object
	*
	* @var OAI_ServerError
	*/
	var $_error;

	/**
	* The repository storage driver object
	*
	* @var OAI_ServerBackend
	*/
	var $backend;

	/**
	* The data storage driver object
	*
	* @var
	*/
	var $_dataDriver;

	/**
	* The request arguments
	*
	* @var array
	*/
	var $args = array();

  /**
   * Allowed GET variables
   *
   * @var array
   */
  var $allowed_args = array('verb', 'identifier','metadataPrefix','from', 'until', 'set', 'resumptionToken');

	/**
	* Constructor
	*
	* @param $options array	parameters to configure the OAI Server
	* @access public 		
	*/
    function OAI_Server($options = null)
	{
		// execute base constructor
		$this->OAI_Base();
		
		// instantiate error object
		include_once 'OAI/Server/Error.php';
		$this->_error =& new OAI_ServerError;		
		
		// set default options
		$this->requestUrl = htmlspecialchars('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
		$this->repositoryDomain	= $_SERVER['SERVER_NAME'];
		
		// assign options
		$this->_parseOptions($options);
		
		if(!strlen($this->options['template_path'])){
			$this->options['template_path'] = OAI_ServerOutput::getDir();
		}
    }

  /**
   * @param $backend OAI_ServerBackend
   */
  function backend($backend)
	{
		$this->backend =& $backend;
		// pass the OAI options to the driver
		$this->backend->setOaiOptions($this->options);
		//var_dump($this->backend);
	}

	function response()
	{
		if(OAI_DEBUG){
			OAI_Base::debug('Processing request from '.$_SERVER['REMOTE_ADDR'], __FILE__, __LINE__);
		}
		// get the request arguments
		if($this->_setArgs()){
      // filter request argumentss
      $this->_filterArgs();
			// process OAI verb

			// check if the verb is set
			if ($this->_isValid('verb', $this->args['verb'])){
				$method = '_'.$this->args['verb'];
				if($content = $this->$method()){
					$this->_showResponse($content);
				}
			}
		}
		// show OAI response
		$this->_showResponse();
	}

	function _GetRecord()
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'GetRecord'", __FILE__, __LINE__);
		}
		if($this->_isValid('identifier', $this->args['identifier'])){
			if($this->_isValid('metadataPrefix', $this->args['metadataPrefix'])){
				//var_dump($this->options);
				
				// get backend data
				$response = $this->backend->getRecord($this->args['identifier'], $this->args['metadataPrefix']);
				// check if backend supports method
				if($response == OAI_METHOD_NOT_SUPPORTED){
					OAI_ServerError::internalServerError('Backend does not provide getRecord() method');
					if(OAI_DEBUG){
						OAI_Base::debug('Backend does not provide getRecord() method', __FILE__, __LINE__);
					}
				}
				// check if we get a result for this record
				if (!$response) {
					$this->_error->addMessage('idDoesNotExist', '', $this->args['identifier']);
					return false;
				}
				
				// compose the GetRecord response
				include_once 'OAI/Server/Output.php';
				$tpl = new OAI_ServerOutput;
				$tpl->setDir($this->options['template_path']);
				return $tpl->getRecord($response);
			}
		}
	}

	function _Identify()
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'Identify'", __FILE__, __LINE__);
		}
		
		$response = $this->backend->identify();
		
		// check if backend supports method
		if($response == OAI_METHOD_NOT_SUPPORTED){
			$record = $this->options;
			// compose response
			include_once 'OAI/Server/Output.php';
			$tpl = new OAI_ServerOutput;
			$tpl->setDir($this->options['template_path']);
			$record['descriptions'][] = $tpl->IdentifyOai($record);
			// should we add some friends?
			if(is_array($record['friends']) && count($record['friends'])){
				$record['descriptions'][] = $tpl->IdentifyFriends($record['friends']);
			}
			
			return $tpl->Identify($record);
		} else {
			return $response;
		}
	}
	
	function _ListIdentifiers()
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'ListIdentifiers'", __FILE__, __LINE__);
		}

		// no resumption token is set
		if(!isset($this->args['resumptionToken'])){
			// metadataPrefix is mandatory
			if(!$this->_isValid('metadataPrefix', $this->args['metadataPrefix'])){
				return false;
			}
			// check the provided OAI arguments
			$validdate = TRUE;
      if(isset($this->args['until']) && !$this->_isValid('until',$this->args['until'])){
        $validdate = FALSE;
			}
			if(isset($this->args['from']) && !$this->_isValid('from',$this->args['from'])){
        $validdate = FALSE;
			}
      // make sure that no invalid dates end up in the response (both should be checked)
      if (! $validdate) {
        return FALSE;
      }
      if (isset($this->args['from']) && isset($this->args['until'])) {
        // check same granularity
        if (!$this->_isValid('from_until', array($this->args['from'], $this->args['until']))) {
          return false;
        }
      }
			/*
			if(isset($this->args['set']) && !$this->_isValid('set',$this->args['set'])){
				return false;
			}
			*/
		} else {
			if(!$this->_isValid('resumptionToken', $this->args['resumptionToken'])){
				return false;
			}
		}
		
		// the token parameters
		$tokenOptions = array(
								'timespan' => $this->options['token_timespan'],
								'requestArgs' => $this->args,
								'completeListSize' => $this->backend->completeListSize($this->args),
								'cursorMax' => $this->options['listidentifiers_max'],
								'container' => $this->options['token_container'],
								'table' => $this->options['token_table'],
								'dsn' => $this->options['token_dsn']
								);
								
		// initiate token object
		include_once 'Token.php';
		if (!$token = OAI_Token::start($tokenOptions)) {
			$this->_error->addMessage('badResumptionToken', '', $this->args['resumptionToken']);
			return false;
		}
		
		// get the request arguments from token session
		$this->args = $token->getRequestArgs();
		
		// get the records from our storage driver
		$response['headers'] = $this->backend->listIdentifiers($this->args, $token->getCursorStart(), $token->getCursorEnd());
		// check if backend supports method
		if($response['headers'] == OAI_METHOD_NOT_SUPPORTED){
			OAI_ServerError::internalServerError('Backend does not provide getRecord() method');
			if(OAI_DEBUG){
				OAI_Base::debug('Backend does not provide listIdentifiers() method', __FILE__, __LINE__);
			}
		}
		
		// did we find some records?
    if(!$response['headers']){
			$this->_error->addMessage('noRecordsMatch');
			return false;
		}
		
		if($tokenString = $token->response()){
			$response['resumption_token'] = $tokenString;
		}
					
		include_once 'OAI/Server/Output.php';
		$tpl = new OAI_ServerOutput;
		$tpl->setDir($this->options['template_path']);
		return $tpl->ListIdentifiers($response);
	}
	
	function _ListMetadataFormats()
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'ListMetadataFormats'", __FILE__, __LINE__);
		}
		
		// get available metadata formats
		if (!$response = $this->backend->listMetadataFormats($this->args['identifier'])) {
			if(isset($this->args['identifier'])){
				$this->_error->addMessage('idDoesNotExist', '', $this->args['identifier']);
			} else {
				$this->_error->addMessage('noMetadataFormats');
			}
			return false;
		}
		
		return $response;
	}
	
	
	function _ListRecords()
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'ListRecords'", __FILE__, __LINE__);
		}
		
		// no resumption token is set
		if(!isset($this->args['resumptionToken'])){
			// metadataPrefix is mandatory
			if(!$this->_isValid('metadataPrefix', $this->args['metadataPrefix'])){
				return false;
			}
			// check the provided OAI arguments
      $validdate = TRUE;
      if(isset($this->args['until']) && !$this->_isValid('until',$this->args['until'])){
        $validdate = FALSE;
      }
      if(isset($this->args['from']) && !$this->_isValid('from',$this->args['from'])){
        $validdate = FALSE;
      }
      // make sure that no invalid dates end up in the response (both should be checked)
      if (! $validdate) {
        return FALSE;
      }
      if (isset($this->args['from']) && isset($this->args['until'])) {
        // check same granularity
        if (!$this->_isValid('from_until', array($this->args['from'], $this->args['until']))) {
          return false;
        }
      }
      /*
      if(isset($this->args['set']) && !$this->_isValid('set',$this->args['set'])){
        return false;
      }
      */
		} else {
			if(!$this->_isValid('resumptionToken', $this->args['resumptionToken'])){
				return false;
			}
		}		
		
		// the token parameters
		$tokenOptions = array(
								'timespan' => $this->options['token_timespan'],
								'requestArgs' => $this->args,
								'completeListSize' => $this->backend->completeListSize($this->args),
								'cursorMax' => $this->options['listrecords_max'],
								'container' => $this->options['token_container'],
								'table' => $this->options['token_table'],
								'dsn' => $this->options['token_dsn']
								);
		// initiate token object
		include_once 'Token.php';
		if (!$token = OAI_Token::start($tokenOptions)) {
			$this->_error->addMessage('badResumptionToken', '', $this->args['resumptionToken']);
			return false;
		}

		// get the request arguments from token session
		$this->args = $token->getRequestArgs();
		
		// get the records from our storage driver
		$response['records'] = $this->backend->listRecords($this->args, $token->getCursorStart(), $token->getCursorEnd());
		
		// check if backend supports method
		if($response['records'] == OAI_METHOD_NOT_SUPPORTED){
			OAI_ServerError::internalServerError('Backend does not provide getRecord() method');
			if(OAI_DEBUG){
				OAI_Base::debug('Backend does not provide listRecords() method', __FILE__, __LINE__);
			}
		}
		if(!$response['records']){
			$this->_error->addMessage('noRecordsMatch');
			return false;
		}
		
		if($tokenString = $token->response()){
			$response['resumption_token'] = $tokenString;
		}
					
		include_once 'OAI/Server/Output.php';
		$tpl = new OAI_ServerOutput;
		$tpl->setDir($this->options['template_path']);
		return $tpl->ListRecords($response);
	}
	
	function _ListSets()
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'ListSets'", __FILE__, __LINE__);
		}
		
		$response = $this->backend->listSets();
		if($response == OAI_METHOD_NOT_SUPPORTED){
			$this->_error->addMessage('noSetHierarchy');
		}
		return $response;
	}
	
	function _setArgs($args = null, $unset = false)
	{

    // if no argument parameter is passed to this method,
		// we take the OAI arguments from the superglobals
		if(is_null($args)){
			// handle different register globals behaviours
			$this->_registerGlobals();
			
			// return request arguments
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (! $this->_checkDuplicateArgs($_SERVER['QUERY_STRING'])) {
          $this->_error->addMessage('badArgument');
        }

        if (!is_array($_GET) || !count($_GET)) {
					$this->_error->addMessage('badArgument');
					return false;
				} else {
					$this->args = $_GET;
          return true;
				}
			} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				if (!is_array($_POST) || !count($_POST)) {
					$this->_error->addMessage('badArgument');
					return false;
				} else {
					$this->args = $_POST;
					return true;
				}
			} else {
				$this->_error->addMessage('badRequestMethod', $_SERVER['REQUEST_METHOD']);
				return false;
			}
		// arguments have been passed to this method, so let's assign them
		} else {
			// if request arguments have already been set, should we unset them?
			if(!$unset){
				// do not unset, instead safely assign new values or overwrite
				// existing values
				if(is_array($args) && count($args)){
					foreach($args as $key => $val){
						$this->args[$key] = $val;
					}
				}
			} else {
				// yes, unset existing values
				$this->args = $args;
			}
			return true;
		}
	}

  function _filterArgs()
  {
    foreach(array_keys($this->args) as $varname) {
      if (! in_array($varname, $this->allowed_args)) {
        unset($this->args[$varname]);
        $this->_error->addMessage('badArgument', $varname);
      }
    }
  }

  function _checkDuplicateArgs($querystring)
  {
    $a = explode('&', $querystring);

    // empty querystring
    if ($a[0] == '') {
      return true;
    }
    $args = array();
    foreach($a as $k_v) {
      $tmp = explode('=', $k_v);
      if (in_array($tmp[0], $args)) {
        return false;
      }
      $args[] = $tmp[0];
    }
    return true;
  }

	function _registerGlobals()
	{
		// register_globals does not need to be set
		if (!version_compare(phpversion(), '4.1.0', '>=')) {
			$_SERVER = $HTTP_SERVER_VARS;
			$_SERVER['REQUEST_METHOD'] = $REQUEST_METHOD;
			$_GET = $HTTP_GET_VARS;
			$_POST = $HTTP_POST_VARS;
		}
	}
	

	function _showResponse($content = '')
	{
		// parse OAI-PMH and verb templates with IT template system
		$tpl = new HTML_Template_IT($this->options['template_path']);
		$tpl->loadTemplatefile('Oai2/OAI-PMH.tpl', true, true);
		$tpl->setCurrentBlock("ENVELOPE");
		$tpl->setVariable('DEFAULT_ENCODING', OAI_RESPONSE_DEFAULT_ENCODING);
		$tpl->setVariable('OAI_NAMESPACE', OAI_RESPONSE_OAI_NAMESPACE);
		$tpl->setVariable('OAI_XSD_INSTANCE', OAI_RESPONSE_XSD_INSTANCE);
		$tpl->setVariable('OAI_SCHEMA_LOCATION', OAI_RESPONSE_OAI_SCHEMA_LOCATION);
		$tpl->setVariable('RESPONSE_DATE', gmstrftime('%Y-%m-%dT%H:%M:%SZ'));
		
		$reqattr = '';
		$args = $this->args;
		if (is_array($args)) {
			foreach ($args as $key => $val) {
				$reqattr .= ' '. htmlspecialchars($key) .'="'. htmlspecialchars($val) .'"';
			}
		}
		$tpl->setVariable('REQUEST_ATTRIBUTES', $reqattr);
		$tpl->setVariable('REQUEST_URL', $this->options['base_url']);
		
		// show error if any occured
		if($this->_error->isError()){
			$tpl->setCurrentBlock("ERROR");
			$tpl->setVariable('ERROR_CODE', $this->_error->getCode());
			$tpl->setVariable('ERROR_DESCRIPTION', $this->_error->getDescription());
			$tpl->parseCurrentBlock("ERROR") ;
		}
		
		// show the response content
		$tpl->setVariable('CONTENT', $content);
		
		$tpl->parseCurrentBlock("ENVELOPE") ;
		$response = $tpl->get();

		
		// gzip response content?
		if (!in_array($this->args['verb'], $this->noCompression) && $this->options['compression'] == 'gzip') {
			// check if zlib extension is loaded (since PHP 4.3.0, zlib is installed
			// by default
			if (!extension_loaded('zlib')){
				if(OAI_DEBUG){
					OAI_Base::debug('You need PHP compiled with the zlib extension to make use of gzip compression', __FILE__, __LINE__);
				}
				// issue HTTP "Internal Server Error" header
				$this->_error->internalServerError('You need PHP compiled with the zlib extension to make use of gzip compression');
			}
			// check if zlib.output_compression is turned off
			if(strtolower(ini_get('zlib.output_compression')) == 'on'){
				if(OAI_DEBUG){
					OAI_Base::debug('zlib.output_compression has to be turned off in your php.ini to avoid problems with ob_gzhandler', __FILE__, __LINE__);
				}
				// issue HTTP "Internal Server Error" header
				$this->_error->internalServerError('zlib.output_compression has to be turned off in your php.ini to avoid problems with ob_gzhandler');
			}
			ob_start('ob_gzhandler');
			if(OAI_DEBUG){
				OAI_Base::debug("set ob_start('ob_gzhandler')", __FILE__, __LINE__);
			}
		} else {
			if(OAI_DEBUG){
				OAI_Base::debug("Compression turned off", __FILE__, __LINE__);
			}
		}
		
		// finally, pass the output to the client
		header('Content-Type:'.OAI_RESPONSE_CONTENT_TYPE);
		echo $response;
		if(OAI_DEBUG){
			OAI_Base::debug("Output the following content with Content-Type ".OAI_RESPONSE_CONTENT_TYPE.":\n".$response, __FILE__, __LINE__);
		}
		exit;
	}

}
