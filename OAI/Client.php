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
	
	$Id: Client.php,v 1.2 2003/07/09 18:41:10 ordnas Exp $
*/

// include OAI base class
require_once 'OAI/Base.php';


/**
* OAI_Client
* Class of OAI client which provides metadata about a OAI repository.
*
* @access   public
* @version  $Id: Client.php,v 1.2 2003/07/09 18:41:10 ordnas Exp $
* @package  OAI
* @author   Sandro Zic <ordnas@php.net> Rewrite for PEAR
* @author   TODO: add PKP contributors
*/
class OAI_Client extends OAI_Base
{
	
	/**
	* The OAI Server Error object
	*
	* @var obj
	*/
	var $_error;

	/**
	* The repository storage driver object
	*
	* @var obj
	*/
	var $backend;

	/**
	* The data storage driver object
	*
	* @var obj
	*/
	var $_dataDriver;

	/**
	* The request arguments
	*
	* @var array
	*/
	var $args = array();

	/**
	* Constructor
	*
	* @param array	Option parameters to configure the OAI Server
	* @access public 		
	*/
    function OAI_Client($options = null)
	{
		// execute base constructor
		$this->OAI_Base();
		
		// instantiate error object
		include_once 'OAI/Client/Error.php';
		$this->_error =& new OAI_ClientError;		
		
		// set default options
		$this->requestUrl = htmlspecialchars('http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']);
		$this->repositoryDomain	= $_SERVER['SERVER_NAME'];
		
		// assign options
		$this->_parseOptions($options);
    }
	
	function backend($backend)
	{
		$this->backend =& $backend;
		// pass the OAI options to the driver
		$this->backend->setOaiOptions($this->options);
		//var_dump($this->backend);
	}

	function request($request)
	{
		if(OAI_DEBUG){
			OAI_Base::debug('Processing request '.$request, __FILE__, __LINE__);
		}
		
		if(is_string($request)){
			
			// parse URL to array
			include_once 'Net/URL.php';
			$url = &new Net_URL($request);
			// compose the base URL
			/*
			if(strlen($url->host.$url->path)){
				echo $this->options['base_url'] = $url->protocol.'://'.$url->user;
				if(strlen($url->pass)){
					$this->options['base_url'] .= ':'.$url->pass;
				}
				if(strlen($this->user)){
					$this->options['base_url'] .= '@';
				}
				$this->options['base_url'] .= $url->host;
				if(strlen($url->port)){
					$this->options['base_url'] .= ':'.$url->port;
				}
				$this->options['base_url'] .= $url->path;
			}
			*/
			$this->args = $url->querystring;
			$this->args['url'] = $request;
			
			if(!strlen($this->args['verb'])){
				return false;
			}
			
			$method = '_'.$this->args['verb'];
			
			return $this->$method();
		}
		/*
		// get the request arguments
		if($this->_setArgs()){
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
		$this->_showResponse();*/
	}
/*
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
			if(isset($this->args['until']) && !$this->_isValid('until',$this->args['until'])){
				return false;
			}
			if(isset($this->args['from']) && !$this->_isValid('from',$this->args['from'])){
				return false;
			}
			/*
			if(isset($this->args['set']) && !$this->_isValid('set',$this->args['set'])){
				return false;
			}
			*//*
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
		if(!$response){
			$this->_error->addMessage('noRecordsMatch');
			return false;
		}
		
		if($tokenString = $token->response()){
			$response['resumption_token'] = $tokenString;
		}
					
		include_once 'OAI/Server/Output.php';
		$tpl = new OAI_ServerOutput;
		
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
	
	*/
	function _ListRecords()
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'ListRecords'", __FILE__, __LINE__);
		}
		
		// this is a hack, we should instead later create a proper HTTP GET request
		ob_start();
		readfile($this->args['url']);
		$response = ob_get_contents();
		ob_end_clean();
		return $response;
	}
	/*
	function _ListSets()
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'ListSets'", __FILE__, __LINE__);
		}
		
		$response = $this->backend->listSets();
		if($response == OAI_METHOD_NOT_SUPPORTED){
			$this->_error->addMessage('noSetHierarchy');
		}
	}
	*/
	function _setArgs($args = null, $unset = false)
	{
		// if no argument parameter is passed to this method,
		// we take the OAI arguments from the superglobals
		if(is_null($args)){
			// handle different register globals behaviours
			$this->_registerGlobals();
			
			// return request arguments
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
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

}
?>