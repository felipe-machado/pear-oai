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
	
	$Id: Output.php,v 1.3 2003/07/09 12:53:36 ordnas Exp $
*/

// IT template system
require_once "HTML/Template/IT.php";

/**
* OAI_ItTemplateDefaultTransform
*
* Helper class to transform IT templates.
*
* @access   public
* @version  $Id: Output.php,v 1.3 2003/07/09 12:53:36 ordnas Exp $
* @package  OAI
* @author   Sandro Zic <ordnas@php.net> Rewrite for PEAR
* @author   Heinrich Stamerjohanns <stamer@uni-oldenburg.de> Original Author
*/
class OAI_ServerOutput
{
	
	var $path = '';
	

	function setDir($path)
	{
		$this->path = $path;	
	}

	function OAI_ServerOutput()
	{
		// root path of our templates
// 		include_once 'PEAR/Config.php';
// 		$config = &PEAR_Config::singleton();
//		$this->path = $config->get('php_dir').'/OAI/Server/Output/';
        $this->path = realpath(dirname(__FILE__) . '/Output/');
	}
	
	function getDir()
	{
		if(strlen($this->path)){
			return $this->path;
		}
		// root path of our templates
// 		require_once 'PEAR/Config.php';
// 		$config = &PEAR_Config::singleton();
// 		return $config->get('php_dir').'/OAI/Server/Output/';
        return realpath(dirname(__FILE__) . '/Output/');
	}
	
	function metadata($record, $tpl_file)
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start to compose metadata markup with file ".$tpl_file, __FILE__, __LINE__);
		}
		
		// compose the metadata
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile($tpl_file, true, true);
		
		// iterate record values
		foreach($record as $key => $val){
			$tpl->setCurrentBlock($key);
			$tpl->setVariable($key, OAI_Base::xmlEncode($val));
			$tpl->parseCurrentBlock($key);
		}
		
		return $tpl->get();
	}

	function header($record)
	{
		// compose the GetRecord response
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile('Oai2/header.tpl', true, true);
		if ($record['deleted']){
			$tpl->setVariable('STATUS', ' status="deleted"');
		}
		$tpl->setVariable('IDENTIFIER', OAI_Base::xmlEncode($record['identifier']));
		$tpl->setVariable('DATESTAMP', OAI_Base::xmlEncode($record['datestamp']));
		// are we supposed to add metadata infos?
		if(strlen($record['metadata'])){
			$tpl->setCurrentBlock('METADATA');
			$tpl->setVariable('METADATA', $record['metadata']);
			$tpl->parseCurrentBlock('METADATA');
		}
		return $tpl->get();
	}
	
	
	function record($record)
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing output 'record'", __FILE__, __LINE__);
		}
		
		// compose the GetRecord response
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile('Oai2/record.tpl', true, true);
		$tpl->setVariable('RECORD', $record);
		return $tpl->get();
	}

	function GetRecord($record)
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'GetRecord'", __FILE__, __LINE__);
		}
		
		// compose the GetRecord response
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile('Oai2/GetRecord.tpl', true, true);
		$tpl->setVariable('RECORD', $record);
		return $tpl->get();
	}

	function Identify($record)
	{

 //   echo '<pre>';
//    print_r($record);
//    echo $this->path;
//    echo '</pre>';
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile('Oai2/Identify.tpl', true, true);
		
		$tpl->setVariable('REPOSITORY_NAME', OAI_Base::xmlEncode($record['repository_name']));
		$tpl->setVariable('BASE_URL', OAI_Base::xmlEncode($record['base_url']));
		$tpl->setVariable('PROTOCOL_VERSION', OAI_Base::xmlEncode(OAI_PROTOCOL_VERSION));
		$tpl->setVariable('EARLIEST_DATESTAMP', OAI_Base::xmlEncode($record['earliest_datestamp']));
		$tpl->setVariable('DELETED_RECORD', OAI_Base::xmlEncode($record['deleted_record']));
		$tpl->setVariable('GRANULARITY', OAI_Base::xmlEncode($record['granularity']));
		$tpl->setVariable('COMPRESSION', OAI_Base::xmlEncode($record['compression']));
		
		
		// add admin emails
		if(is_array($record['admin_emails']) && count($record['admin_emails'])){
			foreach($record['admin_emails'] as $admin_email){
				$tpl->setCurrentBlock('ADMIN_EMAIL');
				$tpl->setVariable('ADMIN_EMAIL', OAI_Base::xmlEncode($admin_email));
				$tpl->parseCurrentBlock('ADMIN_EMAIL');
			}
		}
				
		// should we add some descriptions?
		if(is_array($record['descriptions']) && count($record['descriptions'])){
			foreach($record['descriptions'] as $description){
				$tpl->setCurrentBlock('DESCRIPTION');
				$tpl->setVariable('DESCRIPTION', $description);
				$tpl->parseCurrentBlock('DESCRIPTION');
			}
		}
		
		return $tpl->get();
	}
	

	function IdentifyOai($record)
	{
		//print_r($record);
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile('Oai2/IdentifyOai.tpl', true, true);
		
		$tpl->setVariable('SCHEME', OAI_Base::xmlEncode($record['scheme']));
		$tpl->setVariable('REPOSITORY_IDENTIFIER', OAI_Base::xmlEncode($record['repository_identifier']));
		$tpl->setVariable('DELIMITER', OAI_Base::xmlEncode($record['delimiter']));
		$tpl->setVariable('SAMPLE_IDENTIFIER', OAI_Base::xmlEncode($record['scheme'].$record['delimiter'].$record['repository_identifier'].$record['delimiter'].'123456'));
		
		return $tpl->get();
	}


	function IdentifyFriends($record)
	{
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile('Oai2/IdentifyFriends.tpl', true, true);
		
		foreach($record as $friend){
			$tpl->setCurrentBlock('FRIEND_BASE_URL');
			$tpl->setVariable('FRIEND_BASE_URL', OAI_Base::xmlEncode($friend));
			$tpl->parseCurrentBlock('FRIEND_BASE_URL');
		}
		
		return $tpl->get();
	}
	
	
	function ListIdentifiers($record)
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'ListIdentifiers'", __FILE__, __LINE__);
		}
		
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile('Oai2/ListIdentifiers.tpl', true, true);
		
		$tpl->setVariable('HEADERS', $record['headers']);
		$tpl->setVariable('RESUMPTION_TOKEN', $record['resumption_token']);
					
		return $tpl->get();
	}
	
	function ListMetadataFormats($records)
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'ListMetadataFormats'", __FILE__, __LINE__);
		}
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile('Oai2/ListMetadataFormats.tpl', true, true);

		// iterate metadata formats
		foreach($records as $prefix => $urls){
			$tpl->setCurrentBlock('METADATAFORMAT');
			$tpl->setVariable('PREFIX', OAI_Base::xmlEncode($prefix));
			$tpl->setVariable('SCHEMA', OAI_Base::xmlEncode($urls['schema']));
			$tpl->setVariable('NAMESPACE', OAI_Base::xmlEncode($urls['namespace']));
			$tpl->parseCurrentBlock('METADATAFORMAT');
		}
		
		return $tpl->get();
	}
	
	
	function ListRecords($records)
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'ListRecords'", __FILE__, __LINE__);
		}
		
		// compose the GetRecords response
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile('Oai2/ListRecords.tpl', true, true);
		
		$tpl->setVariable('RECORDS', $records['records']);
		$tpl->setVariable('RESUMPTION_TOKEN', $records['resumption_token']);
		
		return $tpl->get();
	}
	
    /**
     * Added by Jan Pieter Kunst <janpieter.kunst@meertens.knaw.nl>
     */
	function ListSets($records)
	{
		if(OAI_DEBUG){
			OAI_Base::debug("Start processing verb 'ListSets'", __FILE__, __LINE__);
		}
	
		$tpl = new HTML_Template_IT($this->path);
		$tpl->loadTemplatefile('Oai2/ListSets.tpl', true, true);

		// iterate sets
		foreach($records as $set){
			$tpl->setCurrentBlock('SET');
			$tpl->setVariable('SETSPEC', OAI_Base::xmlEncode($set['setspec']));
			if (array_key_exists('setname', $set)) {
                $tpl->setVariable('SETNAME', OAI_Base::xmlEncode($set['setname']));			
			}
			$tpl->parseCurrentBlock('SET');
		}
		return $tpl->get();
	}

}
?>