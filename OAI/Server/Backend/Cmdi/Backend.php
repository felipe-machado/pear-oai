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
	
	$Id: Backend.php,v 1.3 2003/07/09 15:27:41 ordnas Exp $
*/

require_once "OAI/Server/Backend.php";
require_once "DB.php";
require_once "OAI/Server/Output.php";

/**
 * OAI_ServerContainerDB
 * PEAR::DB storage class for fetching OAI data.
 *
 * @access   public
 * @version  $Id: Backend.php,v 1.3 2003/07/09 15:27:41 ordnas Exp $
 * @package  OAI
 * @author   Sandro Zic <ordnas@php.net> Rewrite for PEAR
 * @author   Heinrich Stamerjohanns <stamer@uni-oldenburg.de> Original Author
 */
class OAI_ServerBackendCmdi extends OAI_ServerBackend
{

  /**
   * DB object
   *
   * @var $db DB_mysqli
   */
  var $db = null;

  var $options
    = array(
      /*
    *the table name where we store repository data
    */
      'table' => 'oai',

      /*
      * Delimiter for multiple elements in one table field
      *
      * If we store multiple entries for one element
      * in a single row of our repository table, 'element_delimiter'
      * ist the delimiter for these entries.
      * If you do not do this, do not define 'element_delimiter'
      */
      'delimiter' => ';',

      /*
      * The name of the column where we store the record identifiers.
      * For more information, please consult
      * http://www.openarchives.org/OAI/openarchivesprotocol.html#UniqueIdentifier
      *
      * Also have a look at the 'masquerade_identifier' option in options.php
      */
      'identifier' => 'oai_identifier',

      /*
      * The name of the column where you store your datestamps.
      */
      'datestamp' => 'oai_datestamp',

      /*
      * The name of the column where you store information whether
      * a record has been deleted. Leave it as it is if you do not use
      * this feature.
      */
      'deleted' => 'oai_deleted',

      /**
       * The name of the column where you store sets information of the record
       */
      'sets' => 'oai_sets',
    );

  /**
   * Set a single option
   *
   * @param  string $key  The name of the option
   * @param  mixed  $val  The value of the option
   */
  function setOption($key, $val)
  {
    $this->options[$key] = $val;
  }

  /**
   * Constructor of the container class
   *
   * Initate connection to the database via PEAR::DB
   *
   * @param  string $options Connection data or DB object
   *
   * @return object Returns an error object if something went wrong
   */
  function OAI_ServerBackendCmdi($options = array())
  {
    // assign user-defined options
    foreach ($options as $key => $val) {
      $this->options[$key] = $val;
    }
  }

  /**
   * Connect to database by using the given DSN string
   *
   * @access private
   *
   * @param  string $dsn string | obj
   *
   * @return mixed  Object on error, otherwise bool
   */
  function connect($dsn)
  {
    if (is_string($dsn) || is_array($dsn)) {
      $this->db = DB::Connect($dsn);
    } elseif (get_parent_class($dsn) == "db_common") {
      $this->db = $dsn;
    } elseif (is_object($dsn) && DB::isError($dsn)) {
      /** @var $dsn DB_Error */
      return PEAR::raiseError($dsn->code, PEAR_ERROR_DIE);
    } else {
      return PEAR::raiseError(
        "The given dsn was not valid in file " . __FILE__ . " at line " . __LINE__,
        41,
        PEAR_ERROR_RETURN,
        null,
        null
      );

    }

    if (DB::isError($this->db)) {
      return PEAR::raiseError($this->db->code, PEAR_ERROR_DIE);
    } else {
      $this->db->setFetchMode(DB_FETCHMODE_ASSOC);
      return true;
    }
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
   * @access public
   */
  function getRecord($identifier, $format = 'oai_dc')
  {
    // is identifier masquerading turned on?
    if ($this->oai['masquerade']) {
      $identifier = $this->_stripIdentifier($identifier);
    }
    $query = '';
    switch ($format) {
    case 'oai_dc':
      $identifier_key = $this->options['identifier'];
      $query
        =
        'SELECT oai_datestamp,oai_deleted,oai_sets,dc_identifier,dc_title,dc_creator,dc_subject,dc_description,dc_publisher,dc_contributor,dc_type,dc_format,dc_source,dc_language,dc_relation,dc_coverage,dc_rights,dc_date FROM '
          . $this->options['table'] . ' WHERE ';
      $query .= $identifier_key . ' = ?';
      break;
    case 'cmdi':
      $identifier_key = 'cmdi_identifier';
      $query = 'SELECT ' .
        $this->options['table'] . '.' . $identifier_key . ',' .
        $this->options['table'] . '.' . $this->options['datestamp'] .
        ', cmdi.cmdi_record  ' .
        ' FROM  ' . $this->options['table'] .
        ' JOIN cmdi ON (cmdi.cmdi_identifier=' . $this->options['table'] . '.' . $identifier_key . ') WHERE ' .
        $this->options['table'] . '.' . $identifier_key . ' = ?';
      break;
    }

    $res = $this->db->query($query, array($identifier));
    if (DB::isError($res)) {
      /** @noinspection PhpDynamicAsStaticMethodCallInspection */
      /** @var $res PEAR_Error */
      PEAR::raiseError($res->getMessage());
      /** @var $res  */
    } elseif (!$res->NumRows()) {
      return false;
    }

    // create XML output
    $record = $res->fetchRow();

    $tpl = new OAI_ServerOutput;
    $tpl->setDir($this->options['template_path']);

    $metadataRecord = $record;
    switch ($format) {
    case 'oai_dc':
      unset(
      $metadataRecord[$this->options['datestamp']],
      $metadataRecord[$this->options['sets']],
      $metadataRecord[$this->options['deleted']]
      );
      $tpl_file = 'Default/metadata_oai_dc.tpl';
      break;
    case 'cmdi':
      unset(
      $metadataRecord[$identifier_key],
      $metadataRecord[$this->options['datestamp']]
      );
      // remove XML prolog for embedding in OAI
      $metadataRecord['cmdi_record'] = str_replace(
        '<?xml version="1.0" encoding="UTF-8"?>', '', $metadataRecord['cmdi_record']
      );
      $metadataRecord['cmdi_record'] = str_replace(
        '<?xml version="1.0" encoding="UTF-8" standalone="no"?>', '', $metadataRecord['cmdi_record']
      );
      $tpl_file = 'Cmdi/metadata_cmdi.tpl';
      break;
    }


    $headerRecord['metadata'] = $tpl->metadata($metadataRecord, $tpl_file);

    if (OAI_DEBUG) {
      OAI_Base::debug("Composed metadata markup", __FILE__, __LINE__);
    }

    // is identifier masquerading turned on?
    if ($this->oai['masquerade']) {
      $headerRecord['identifier'] = $this->_masqueradeIdentifier($record[$identifier_key]);
    } else {
      $headerRecord['identifier'] = $record[$identifier_key];
    }
    // see how we handle deleted records
    if (
      $record[$this->options['deleted']]
      && ($this->oai['deleted_record'] == 'transient' || $this->oai['deleted_record'] == 'persistent')
    ) {
      $headerRecord['deleted'] = true;
    }
    // the record datestamp
    $headerRecord['datestamp'] = $this->_mysqldatetime2datestamp($record[$this->options['datestamp']]);

    $tpl = new OAI_ServerOutput;
    $tpl->setDir($this->options['template_path']);

    return $tpl->header($headerRecord);
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
   * @param $args        array    The optional OAI arguments
   * @param $cursorStart int  Token cursor starting point
   * @param $cursorEnd   int    Token cursor ending point
   * @return string
   *
   * @access public
   */
  function listIdentifiers($args, $cursorStart, $cursorEnd)
  {

    switch ($args['metadataPrefix']) {
    case 'oai_dc':
      $identifier_key = $this->options['identifier'];
      $query = 'SELECT ' .
        $identifier_key . ',' .
        $this->options['datestamp'] . ',' .
        $this->options['sets'] . ' ' .
        'FROM ' .
        $this->options['table'];
      break;
    case 'cmdi':
      $identifier_key = 'cmdi_identifier';
      $query = 'SELECT ' .
        'cmdi.' . $identifier_key . ',' .
        $this->options['table'] . '.' . $this->options['datestamp'] . ',' .
        $this->options['table'] . '.' . $this->options['sets'] . ' ' .
        ' FROM  ' . $this->options['table'] .
        ' JOIN cmdi ON (cmdi.cmdi_identifier=' . $this->options['table'] . '.' . $identifier_key . ')';
      break;
    }


    $query_parts = array();
    if (isset($args['until'])) {
      $query_parts[] = $this->options['datestamp'] . ' <= "' . $this->db->escapeSimple($this->_datestamp2mysqldatetime($args['until'], 'until')) . '"';
    }
    if (isset($args['from'])) {
      $query_parts[] = $this->options['datestamp'] . ' >= "' . $this->db->escapeSimple($this->_datestamp2mysqldatetime($args['from'], 'from')) . '"';
    }
    if (isset($args['set'])) {
      $query_parts[] = $this->options['sets'] . ' LIKE "%' . $this->db->escapeSimple($args['set']) . '%"';
    }

    if (count($query_parts)) {
      $query .= ' WHERE ';
      foreach ($query_parts as $query_part) {
        $query .= $query_part . ' AND ';
      }
      $query = substr($query, 0, -4);
    }
//		$query .= ' ORDER BY ' . $identifier_key;

    $res = $this->db->limitQuery($query, $cursorStart, ($cursorEnd - $cursorStart));

    if (DB::isError($res)) {
      PEAR::raiseError($res->getMessage());
    } elseif (!$res->NumRows()) {
      return false;
    } else {
      // we got records, so compose the response
      $tpl = new OAI_ServerOutput;
      $tpl->setDir($this->options['template_path']);

      $response = '';
      while ($row = $res->fetchRow()) {
        // is identifier masquerading turned on?
        if ($this->oai['masquerade']) {
          $row['identifier'] = $this->_masqueradeIdentifier($row[$identifier_key]);
        } else {
          $row['identifier'] = $row[$identifier_key];
        }
        // see how we handle deleted records
        if (
          $row[$this->options['deleted']]
          && ($this->oai['deleted_record'] == 'transient' || $this->oai['deleted_record'] == 'persistent')
        ) {
          $row['deleted'] = true;
        }
        // the record datestamp
        $row['datestamp'] = $this->_mysqldatetime2datestamp($row[$this->options['datestamp']]);

        $response .= $tpl->header($row);
      }
      return $response;
    }
  }

  function completeListSize($args = array())
  {
    $query = 'SELECT COUNT(*) FROM ' . $this->options['table'];

    $query_parts = array();
    if (isset($args['until'])) {
      $query_parts[] = $this->options['datestamp'] . ' <= "' . $this->db->escapeSimple($this->_datestamp2mysqldatetime($args['until'], 'until')) . '"';
    }
    if (isset($args['from'])) {
      $query_parts[] = $this->options['datestamp'] . ' >= "' . $this->db->escapeSimple($this->_datestamp2mysqldatetime($args['from'], 'from')) . '"';
    }
    if (isset($args['set'])) {
      $query_parts[] = $this->options['sets'] . ' LIKE "%' . $this->db->escapeSimple($args['set']) . '%"';
    }

    if (count($query_parts)) {
      $query .= ' WHERE ';
      foreach ($query_parts as $query_part) {
        $query .= $query_part . ' AND ';
      }
      $query = substr($query, 0, -4);
    }

    //echo $query;

    return $this->db->getOne($query);
  }

  /**
   * Method for verb "ListMetadataFormats".
   *
   * This verb is used to retrieve the metadata formats available from a repository.
   * An optional argument restricts the request to the formats available for a specific
   * item.
   *
   * @access public
   */
  function listMetadataFormats($identifier = null)
  {
    if (strlen($identifier)) {
      // check if we get a result for this record
      // TODO: truely show metadata formats of a specific record when issuing
      // ListMetadataFormats request with identifier argument
      if (!$this->getRecord($identifier)) {
        return false;
      }
    }

    if (!is_array($this->oai['metadata_formats']) || !count($this->oai['metadata_formats'])) {
      return false;
    }

    include_once 'OAI/Server/Output.php';
    $tpl = new OAI_ServerOutput;
    $tpl->setDir($this->options['template_path']);

    return $tpl->ListMetadataFormats($this->oai['metadata_formats']);
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
   * @param $args        array    The optional OAI arguments
   * @param $cursorStart int  Token cursor starting point
   * @param $cursorEnd   int    Token cursor ending point
   * @return string
   *
   * @access public
   */
  function listRecords($args, $cursorStart, $cursorEnd)
  {

    switch ($args['metadataPrefix']) {
    case 'oai_dc':
      $identifier_key = $this->options['identifier'];
      $query
        =
        'SELECT oai_datestamp,oai_deleted,oai_sets,dc_identifier,dc_title,dc_creator,dc_subject,dc_description,dc_publisher,dc_contributor,dc_type,dc_format,dc_source,dc_language,dc_relation,dc_coverage,dc_rights,dc_date FROM '
          . $this->options['table'];
      break;
    case 'cmdi':
      $identifier_key = 'cmdi_identifier';
      $query = 'SELECT ' .
        $this->options['table'] . '.' . $identifier_key . ',' .
        $this->options['table'] . '.' . $this->options['datestamp'] .
        ', cmdi.cmdi_record  ' .
        ' FROM  ' . $this->options['table'] .
        ' JOIN cmdi ON (cmdi.cmdi_identifier=' . $this->options['table'] . '.' . $identifier_key . ')';
      break;
    }

    $query_parts = array();
    if (isset($args['until'])) {
      $query_parts[] = $this->options['datestamp'] . ' <= "' . $this->db->escapeSimple($this->_datestamp2mysqldatetime($args['until'], 'until')) . '"';
    }
    if (isset($args['from'])) {
      $query_parts[] = $this->options['datestamp'] . ' >= "' . $this->db->escapeSimple($this->_datestamp2mysqldatetime($args['from'], 'from')) . '"';
    }
    if (isset($args['set'])) {
      $query_parts[] = $this->options['sets'] . ' LIKE "%' . $this->db->escapeSimple($args['set']) . '%"';
    }

    if (count($query_parts)) {
      $query .= ' WHERE ';
      foreach ($query_parts as $query_part) {
        $query .= $query_part . ' AND ';
      }
      $query = substr($query, 0, -4);
    }
//		$query .= ' ORDER BY ' . $identifier_key;
//		echo $query;

    $res = $this->db->limitQuery($query, $cursorStart, ($cursorEnd - $cursorStart));

    if (DB::isError($res)) {
      PEAR::raiseError($res->getMessage());
    } elseif (!$res->NumRows()) {
      return false;
    } else {
      // we got records, so compose the response
      $tpl = new OAI_ServerOutput;
      $tpl->setDir($this->options['template_path']);

      // get the template for metadata output

      switch ($args['metadataPrefix']) {
      case 'oai_dc':
        $metadataTpl = 'Default/metadata_oai_dc.tpl';
        break;
      case 'cmdi':
        $metadataTpl = 'Cmdi/metadata_cmdi.tpl';
        break;
      }

      $response = '';
      while ($row = $res->fetchRow()) {
        $metadataRecord = $this->_escapeEntities($row, $args['metadataPrefix']);

        switch ($args['metadataPrefix']) {
        case 'oai_dc':
          unset(
          $metadataRecord[$this->options['datestamp']],
          $metadataRecord[$this->options['sets']],
          $metadataRecord[$this->options['deleted']]
          );
          break;
        case 'cmdi':
          unset(
          $metadataRecord[$identifier_key],
          $metadataRecord[$this->options['datestamp']]
          );
          // remove XML prolog for embedding in OAI
          $metadataRecord['cmdi_record'] = str_replace(
            '<?xml version="1.0" encoding="UTF-8"?>', '', $metadataRecord['cmdi_record']
          );
          $metadataRecord['cmdi_record'] = str_replace(
            '<?xml version="1.0" encoding="UTF-8" standalone="no"?>', '', $metadataRecord['cmdi_record']
          );
          break;
        }

        $headerRecord['metadata'] = $tpl->metadata($metadataRecord, $metadataTpl);

        // is identifier masquerading turned on?
        if ($this->oai['masquerade']) {
          $headerRecord['identifier'] = $this->_masqueradeIdentifier($row[$identifier_key]);
        } else {
          $headerRecord['identifier'] = $row[$identifier_key];
        }
        // see how we handle deleted records
        if (
          $row[$this->options['deleted']]
          && ($this->oai['deleted_record'] == 'transient' || $this->oai['deleted_record'] == 'persistent')
        ) {
          $headerRecord['deleted'] = true;
        }
        // the record datestamp
        $headerRecord['datestamp'] = $this->_mysqldatetime2datestamp($row[$this->options['datestamp']]);

        $response .= $tpl->record($tpl->header($headerRecord));
      }
      return $response;
    }
  }

  /**
   * Method for verb "ListSets".
   *
   * This verb is used to retrieve the set structure of a repository, useful for
   * selective harvesting.
   *
   * @access public
   */
  function listSets()
  {

    $query = 'SELECT DISTINCT ' .
      $this->options['sets'] . ' AS setspec, oai_setname as setname ' .
      'FROM ' .
      $this->options['table'] .
      ' WHERE ' . $this->options['sets'] . " <> ''";

    $sets = $this->db->getAll($query, DB_FETCHMODE_ASSOC);

    include_once 'OAI/Server/Output.php';
    $tpl = new OAI_ServerOutput;
    $tpl->setDir($this->options['template_path']);

    return $tpl->ListSets($sets);
  }

  private function _escapeEntities($record, $format)
  {
    if ($format == 'cmdi') {
      return $record;
    }

    $retval = array();
    foreach ($record as $k => $v) {
      $retval[$k] = htmlspecialchars($v, ENT_NOQUOTES);
    }
    return $retval;
  }
}
