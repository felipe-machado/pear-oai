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
	
	$Id: Token.php,v 1.9 2003/04/27 13:47:15 ordnas Exp $
*/

/**
* OAI_Token
* This class manages the resumption token feature.
*
* @access   public
* @version  $Id: Token.php,v 1.9 2003/04/27 13:47:15 ordnas Exp $
* @package  OAI
* @author   Sandro Zic <ordnas@php.net> Rewrite for PEAR
* @author   Heinrich Stamerjohanns <stamer@uni-oldenburg.de> Original Author
*/
class OAI_Token
{
	
	/**
	* The token arguments
	*
	* @var array
	*/
	var $tokenOptions = array();

	
	/**
	* Set the token options
	*
	* @param array $tokenOptions	The options
	* @access public
	*/
	function setTokenOptions($tokenOptions)
	{
		$this->tokenOptions = $tokenOptions;
	}

		// TODO: What about idempotency of resumption tokens?
		// Do we have to check, if the complete list size has changed during
		// selective harvesting?

	function &start($tokenOptions)
	{
		// include PEAR::HTTP_Session, a wrapper
		// for the PHP session functions
		include_once 'HTTP/Session.php';
		
		// see if we should use DB to store session
		switch(strtolower($tokenOptions['container'])){
			case 'db':
				if(!is_null($tokenOptions['dsn'])){
					$sessOpt['dsn'] = $tokenOptions['dsn'];
					$sessOpt['table'] = $tokenOptions['table'];
					HTTP_Session::setContainer('DB', $sessOpt);
					if(OAI_DEBUG){
						OAI_Base::debug('Write session to DB. DSN: '.
										$sessOpt['dsn'].', table: '.$sessOpt['table'],
										__FILE__, __LINE__);
					}
				}
				break;
		}
		
		// we do not set a cookie
		HTTP_Session::useCookies(false);
		
		// the token string is empty, so we start a new token session
		if (!strlen($tokenOptions['requestArgs']['resumptionToken'])) {
				// start a new token cursor span
				$tokenOptions['cursorStart'] = 0;
				$tokenOptions['cursorEnd'] = $tokenOptions['cursorMax'];
		} else {
			// a token supposedly exists so set it as the session id
			//HTTP_Session::id($tokenOptions['requestArgs']['resumptionToken']);
			HTTP_Session::start('SessionID', $tokenOptions['requestArgs']['resumptionToken']);
			$sessToken = HTTP_Session::get('sessToken');
			// check for valid resumption Token aka session
			if (!is_array($sessToken) && !count($sessToken)) {
        // JP toegevoegd: anders wordt er door de destructor een lege sessie in de database bewaard
        HTTP_Session::destroy();
        if(OAI_DEBUG){
					OAI_Base::debug('The provided token '.$tokenOptions['requestArgs']['resumptionToken'].' is invalid', __FILE__, __LINE__);
				}
				return false;
			} elseif(str_replace(array('T', 'Z'), array(' ', ''), $sessToken['expirationDate']) < gmstrftime('%Y-%m-%d %H:%M:%S', time())) {
				// here we checked if the expiration date of the resumption
				// token is still within the timespan, but it's not
				
				// let's delete the old token session
				HTTP_Session::destroy();
				
				if(OAI_DEBUG){
					OAI_Base::debug('Resumption token exceeded expiration date ('.str_replace(array('T', 'Z'), array(' ', ''), $sessToken['expirationDate']).' + '.$tokenOptions['timespan'].' seconds < '.gmstrftime('%Y-%m-%d %H:%M:%S', time()).')', __FILE__, __LINE__);
					OAI_Base::debug('Destroyed invalid token session', __FILE__, __LINE__);
				}
				return false;
			} else {
				// compose the new token cursor by adding the specified maximum number of
				// result set records to the token cursor
				$tokenOptions['cursorStart'] = $sessToken['cursorStart'] + $tokenOptions['cursorMax'];
				$tokenOptions['cursorEnd'] = $sessToken['cursorEnd'] + $tokenOptions['cursorMax'];
				
				// assign request arguments
				$tokenOptions['requestArgs'] = $sessToken['requestArgs'];
				// let's delete the old token session
				HTTP_Session::destroy();
				
				if(OAI_DEBUG){
					OAI_Base::debug('Assigned token options from session and destroyed used-up token session', __FILE__, __LINE__);
				}
			}
		}
		
		// remember the token session for next request, but only if there are more
		// records to show
		
		if(OAI_DEBUG){
			OAI_Base::debug('Check if there are more records to be harvested with resumption token', __FILE__, __LINE__);
			OAI_Base::debug('completeListSize: '.$tokenOptions['completeListSize'], __FILE__, __LINE__);
			OAI_Base::debug('cursorEnd: '.$tokenOptions['cursorEnd'], __FILE__, __LINE__);
		}

		if($tokenOptions['completeListSize'] > $tokenOptions['cursorEnd']){
			// calculate the expiration date
			$tokenOptions['expirationDate'] = gmstrftime('%Y-%m-%dT%H:%M:%SZ', time()+$tokenOptions['timespan']);
			// start a new session and get the new token string
			//$tokenOptions['newResumptionToken'] = md5(time()*rand(2,4));
			HTTP_Session::start();
			if(OAI_DEBUG){
				OAI_Base::debug('Started session', __FILE__, __LINE__);
			}
			$tokenOptions['newResumptionToken'] = HTTP_Session::id();
			if(OAI_DEBUG){
				OAI_Base::debug('Assigned new resumption token '.$tokenOptions['newResumptionToken'].' as session id', __FILE__, __LINE__);
			}
			/*HTTP_Session::setExpire(time()+$tokenOptions['timespan']);
			if(OAI_DEBUG){
				OAI_Base::debug('Set session expire time to '.time()+$tokenOptions['timespan'], __FILE__, __LINE__);
			}*/
			HTTP_Session::set('sessToken', $tokenOptions);
			if(OAI_DEBUG){
				OAI_Base::debug('Assign $tokenOptions to $_SESSION', __FILE__, __LINE__);
			}
			// write and close the session
			HTTP_Session::pause();
			if(OAI_DEBUG){
				OAI_Base::debug('Write and close session', __FILE__, __LINE__);
			}
			if(OAI_DEBUG){
				OAI_Base::debug('Created a new resumption token session', __FILE__, __LINE__);
				OAI_Base::debug('expirationDate: '.$tokenOptions['expirationDate'], __FILE__, __LINE__);
				OAI_Base::debug('newResumptionToken: '.$tokenOptions['newResumptionToken'], __FILE__, __LINE__);
			}
		}
		
		// initiate resumption token object
		
		$token = new OAI_Token;
		$token->setTokenOptions($tokenOptions);
		return $token;
	}
	
	/**
	* Get the OAI request arguments
	*
	* @access public
	* @return array
	*/
	function getRequestArgs()
	{
		return $this->tokenOptions['requestArgs'];
	}

	function getCursorStart()
	{
		return $this->tokenOptions['cursorStart'];
	}
		
	function getCursorEnd()
	{
		return $this->tokenOptions['cursorEnd'];
	}

	function response()
	{
		// do we have to add a resumption token?
		//print_r($this->tokenArgs);
		if($this->tokenOptions['completeListSize'] > $this->tokenOptions['cursorEnd']){
			$tokenString =
				'<resumptionToken expirationDate="'.$this->tokenOptions['expirationDate'].'" '. 
					'completeListSize="'.$this->tokenOptions['completeListSize'].'" '. 
					'cursor="'.$this->tokenOptions['cursorStart'].'">'.$this->tokenOptions['newResumptionToken'].'</resumptionToken>';
			return $tokenString;
		} else {
			// we have to show an empty resumption token, if this is the end of a token session
			if(strlen($this->tokenOptions['requestArgs']['resumptionToken'])){
				$tokenString =
					'<resumptionToken'. 
						'completeListSize="'.$this->tokenOptions['completeListSize'].'" '. 
						'cursor="'.$this->tokenOptions['cursorStart'].'"></resumptionToken>';
				return $tokenString;
			} else {
				return false;
			}
		}
	}
	
}
?>