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
	
	$Id: Backend.php,v 1.2 2003/07/09 15:27:11 ordnas Exp $
*/

define("OAI_METHOD_NOT_SUPPORTED", -4);

/**
* OAI_ServerBackend
* Abstract storage class for fetching OAI data.
*
* @access   public
* @version  $Id: Backend.php,v 1.2 2003/07/09 15:27:11 ordnas Exp $
* @package  OAI
* @author   Sandro Zic <ordnas@php.net> Rewrite for PEAR
* @author   Heinrich Stamerjohanns <stamer@uni-oldenburg.de> Original Author
*/
class OAI_ServerBackend
{

	/**
	* The OAI options
	*
	* @var array
	*/
	var $oai;

	/**
	* The OAI arguments
	*
	* @var array
	*/
	var $args;

	/**
	* The OAI identifier prefix composed of the OAI scheme, delimiter, repository
	* identifier.
	*
	* @var string
	*/
	var $oaiprefix;

    /**
     * Constructor
     *
     * Has to be overwritten by each storage class
     *
     * @access public
     */
    function OAI_ServerBackend()
    {
    }

	/**
	* Pass the OAI options to the container
	*
	* @param array $options	The OAI options
	* @access public
	*/
	function setOaiOptions($options)
	{
		$this->oai = $options;
	}

	/**
	* Pass the OAI arguments to the container
	*
	* @param array $args	The OAI arguments
	* @access public
	*/
	function setArgs($args)
	{
		$this->args = $args;
	}

    /**
    * Method for verb "GetRecord".
    *
	* This verb is used to retrieve an individual metadata record from a repository.
	* Required arguments specify the identifier of the item from which the record is 
	* requested and the format of the metadata that should be included in the record.
	* Depending on the level at which a repository tracks deletions, a header with a 
	* "deleted" value for the status attribute may be returned, in case the metadata 
	* format specified by the metadataPrefix is no longer available from the repository
	* or from the specified item.
	*
    * Has to be overwritten by each storage class
    *
    * @access public
    */
    function getRecord() 
    {
		return OAI_METHOD_NOT_SUPPORTED;
    }

    /**
    * Method for verb "Identify".
    *
	* This verb is used to retrieve information about a repository.  Some of the 
	* information returned is required as part of the OAI-PMH.  Repositories may 
	* also employ the Identify verb to return additional descriptive information.
	*
    * Has to be overwritten by each storage class
    *
    * @access public
    */
    function identify() 
    {
		return OAI_METHOD_NOT_SUPPORTED;
    }

    /**
    * Method for verb "ListIdentifiers".
    *
	* This verb is an abbreviated form of ListRecords, retrieving only headers rather
	* than records. Optional arguments permit selective harvesting of headers based on 
	* set membership and/or datestamp. Depending on the repository's support for 
	* deletions, a returned header may have a status attribute of "deleted" if a record
	* matching the arguments specified in the request has been deleted.
	*
    * Has to be overwritten by each storage class
    *
    * @access public
    */
    function listIdentifiers() 
    {
		return OAI_METHOD_NOT_SUPPORTED;
    }

    /**
    * Method for verb "ListMetadataFormats".
    *
	* This verb is used to retrieve the metadata formats available from a repository.
	* An optional argument restricts the request to the formats available for a specific
	* item.
	*
    * Has to be overwritten by each storage class
    *
    * @access public
    */
    function listMetadataFormats() 
    {
		return OAI_METHOD_NOT_SUPPORTED;
    }

    /**
    * Method for verb "ListRecords".
    *
	* This verb is used to harvest records from a repository.  Optional arguments 
	* permit selective harvesting of records based on set membership and/or datestamp.
	* Depending on the repository's support for deletions, a returned header may have a 
	* status attribute of "deleted" if a record matching the arguments specified in the 
	* request has been deleted. No metadata will be present for records with deleted 
	* status.
	*
    * Has to be overwritten by each storage class
    *
    * @access public
    */
    function listRecords() 
    {
		return OAI_METHOD_NOT_SUPPORTED;
    }

    /**
    * Method for verb "ListSets".
    *
	* This verb is used to retrieve the set structure of a repository, useful for 
	* selective harvesting.
	*
    * Has to be overwritten by each storage class
    *
    * @access public
    */
    function listSets() 
    {
		return OAI_METHOD_NOT_SUPPORTED;
    }

	function completeListSize()
    {
		return OAI_METHOD_NOT_SUPPORTED;
    }	
	
	function _masqueradeIdentifier($identifier)
	{
		if(!strlen($this->oaiprefix)){
			// compose record identifier from item identifier
			$this->oaiprefix = $this->oai['scheme'].$this->oai['delimiter'].$this->oai['repository_identifier'].$this->oai['delimiter'];
		}
		return $this->oaiprefix.$identifier; 
	}
	
	function _stripIdentifier($identifier)
	{
		if(!strlen($this->oaiprefix)){
			// compose record identifier from item identifier
			$this->oaiprefix = $this->oai['scheme'].$this->oai['delimiter'].$this->oai['repository_identifier'].$this->oai['delimiter'];
		}
		return str_replace($this->oaiprefix, '', $identifier);
	}

  function _datestamp2mysqldatetime($parameter, $type)
  {
    if (preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}$)/', $parameter)) {
      if ($type == 'from') {
        return $parameter . ' 00:00:00';
      } elseif ($type == 'until') {
        return $parameter . ' 23:59:59';
      }
    } else {
      return str_replace(array('T', 'Z'), array(' ', ''), $parameter);
    }
  }

  function _mysqldatetime2datestamp($value)
  {
      return str_replace(' ', 'T', $value . 'Z');

  }
}

