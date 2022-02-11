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
	
	$Id: options.php,v 1.8 2003/07/09 12:53:36 ordnas Exp $
*/

/**
 * PEAR::OAI Configuration File
 *
 * This configuration file holds all configuration parameters for PEAR::OAI.
 *
 * Please adjust the parameters to your needs.
 */

/**
 * The base URL of the script cmdiexecuting the OAI Server.
 *
 * Actually, this is the URL to the PHP script where you implemented
 * the OAI Server, e.g. like tests/oai_server.php in this package.
 *
 * $config['base_url'] = '';  You can leave this empty, then the
 * OAI class will automatically compose the value.
 *
 * $config['base_url'] = 'http://www.example.com/oai_server.php';
 * This is an example URL
 */
$options['base_url'] = '';

/**
 * The name of our OAI repository
 */

$options['repository_name'] = utf8_decode('Repositorio OAI PasaLaPágina');

//	$options['repository_name'] = 'Repository Implemented with PHP using PEAR::OAI';

/**
 * The scheme our repository
 *
 * Usually, you would name this 'oai'.
 */
$options['scheme'] = 'oai';

/**
 * Email of repository administrators.
 */
//	$options['admin_emails'] = array('me@example.com', 'you@example.com', 'him@example.com');
$options['admin_emails'] = array('alex@pasalapagina.com', 'andres@pasalapagina.com', 'felipe@pasalapagina.com');

/**
 * The earliest datastamp in your repository
 */
// $options['earliest_datestamp'] = '2000-01-01T00:00:00Z';
$options['earliest_datestamp'] = gmdate('Y-m-d\TH:i:s\Z', strtotime('2009-09-04 00:00:00'));

/**
 * How your repository handles deletions
 * 'no'      The repository does not maintain status about deletions.
 * It MUST NOT reveal a deleted status.
 * 'persistent'  The repository persistently keeps track about deletions
 * with no time limit. It MUST consistently reveal the status
 * of a deleted record over time.
 * 'transient'    The repository does not guarantee that a list of deletions is
 * maintained. It MAY reveal a deleted status for records.
 *
 * If your database keeps tracks of deleted records change accordingly
 * Currently if $record['deleted'] in not NULL, $status_deleted is set.
 * some lines in listidentifiers.php, listrecords.php, getrecords.php
 * must be changed to fit the condition for your database
 *
 * Please consult for more information:
 * http://www.openarchives.org/OAI/openarchivesprotocol.html#deletion
 */
//$options['deleted_record'] = 'transient';
$options['deleted_record'] = 'persistent';

/**
 * Set the datestamp granularity of your repository.
 * YYYY-MM-DD          granularity is days
 * YYYY-MM-DDThh:mm:ssZ    granularity is seconds
 *
 * Please consult for more information:
 * http://www.openarchives.org/OAI/openarchivesprotocol.html#Datestamp
 */
$options['granularity'] = 'YYYY-MM-DD';

/**
 * Delimiter of OAI identifier syntax
 *
 * Please consult for more information:
 * http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify
 */
$options['delimiter'] = ':';

/**
 * Identifier name
 *
 * Please consult for more information:
 * http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify
 */
// $options['repository_identifier'] = 'example.com';
$options['repository_identifier'] = 'pasalapagina.com';

/**
 * Specify if our repository works with masqueraded identifiers.
 *
 * This is useful, if you do not store the full OAI identifier, e.g.
 *
 * oai:aName:192
 *
 * in your database table for example, but the ID of the item only, e.g.
 *
 * 192
 *
 * If you turn on masquerading, outgoing identifiers will be complemented
 * with the scheme and repository name (separated by the delimiter) as
 * specified above. Ingoing identifiers will be stripped of by the
 * [scheme][delimiter][repository_name][delimiter] string.
 *
 * false  Turn off masquerading
 * true  Turn on masquerading
 */
$options['masquerade'] = true;


/**
 * Friends of your repository.
 *
 * Actually, a collection of base URLs to other repositories (here stored
 * in a numeric array).
 *
 * Please consult for more information:
 * http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify
 *
 * A list of OAI repositories is available at
 * http://www.openarchives.org/Register/BrowseSites.pl
 */
//$options['friends'] = array(
//  'http://cdsweb.cern.ch/oai',
//  'http://alcme.oclc.org/xtcat/servlet/OAIHandler'
//);
$options['friends'] = array();

/**
 * Maximum mumber of the records to deliver upon 'ListRecords' request.
 * If there are more records to deliver, a ResumptionToken will be generated.
 *
 * Please consult for more information:
 * http://www.openarchives.org/OAI/openarchivesprotocol.html#FlowControl
 */
$options['listrecords_max'] = 100;

/**
 * Maximum mumber of identifiers to deliver (verb is ListIdentifiers)
 * If there are more identifiers to deliver, a ResumptionToken will be generated.
 *
 * Please consult for more information:
 * http://www.openarchives.org/OAI/openarchivesprotocol.html#FlowControl
 */
$options['listidentifiers_max'] = 1000;


/**
 * Maximum mumber of sets to deliver (verb is ListSets)
 * If there are more identifiers to deliver, a ResumptionToken
 * will be generated.
 *
 * Please consult for more information:
 * http://www.openarchives.org/OAI/openarchivesprotocol.html#FlowControl
 */
$options['listsets_max'] = 50;

/**
 * Validity of resumption tokens in seconds
 *
 * E.g. 86400 (= 24*3600) means that after 24 hours the resumption tokens
 * become invalid.
 */
$options['token_timespan'] = 86400;

/**
 * Store resumption token sessions in database?
 *
 * The data of your resumption token sessions (e.g. the cursor position)
 * is by default stored in your filesystem. Optionally, it can be stored
 * in your database. Therefore you have to create the following table
 * in the _same_ database where your OAI records are stored:
 *
 * CREATE TABLE `oai_token` (
 * `id` CHAR( 32 ) NOT NULL ,
 * `expiry` INT UNSIGNED NOT NULL ,
 * `data` TEXT NOT NULL ,
 * PRIMARY KEY ( `id` )
 * ) COMMENT = 'This table stores the resumption token sessions.';
 *
 * You can change the name of this table and assign the new name to
 * the 'token_table' option (see below), but _never_ change the
 * structure of the table!
 *
 * Options:
 * 'file'  Use filesystem instead of database.
 * 'db'  Use database instead of filesystem.
 */
// $options['token_container'] = 'file';
$options['token_container'] = 'db';

/**
 * Name of the database table where we store token session data.
 *
 * If you chose to store the session data of resumption tokens
 * in the database, then you can adjust the name of the table where
 * we store the data with this option.
 *
 * The default value is 'oai_token'.
 */
$options['token_table'] = 'oai_token';

/**
 * Output compression.
 *
 * Currently only gzip is supported (you need output buffering turned on,
 * and PHP compiled with zlib. The later is a core component since PHP
 * 4.3.0.). The client MUST send "Accept-Encoding: gzip" to actually receive
 * compressed output, but this is handled by PHP automatically.
 *
 * Please consult for more information:
 * http://www.openarchives.org/OAI/openarchivesprotocol.html#ResponseCompression
 * and
 * http://www.php.net/manual/en/function.ob-gzhandler.php
 *
 * $options['compression'] = '';      turn off output compression
 * $options['compression'] = 'gzip'    use gzip encoding
 */
$options['compression'] = '';

/**
 * The metadata prefixes available.
 *
 * Defined as a numeric array, currently supported:
 *
 * 'oai_dc'  Unqualified Dublin Core
 *
 * Delete metadata formats that you do not want your repository to support.
 */
//$options['metadata_formats']
//  = array(
//  'oai_dc' => array(
//    'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
//    'namespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/'
//  )
//);
$options['metadata_formats']
  = array(
  'oai_dc' => array(
    'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
    'namespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/'
  ),
  'cmdi' => array(
    'schema' => 'http://www.clarin.eu/cmd/xsd/minimal-cmdi.xsd',
    'namespace' => 'http://www.clarin.eu/cmd/'
  )
);


/**
 * The directory where the OAI templates reside.
 *
 * These templates are written for PEAR::IT[X] templates system and contain
 * the structure of the OAI responses, especially the metadata formats.
 *
 * You can leave this empty, then the OAI class will automatically compose
 * the value.
 *
 * HAS BEEN DEPRECATED WITH VERS. 0.4.0 AND REINTRODUCED WITH VERS. 0.5.0
 */
// $options['templates_path'] = '';
$options['templates_path'] = $options['template_path'] = realpath('OAI/Server/Output/');

/**
 * Set or unset debug mode. These are the possible parameters:
 *
 * false  No debug information is collected.
 * true  Turns on debug, which means that debug information will
 * be written to the file specified in the error_log directive
 * of your php.ini. Read more about this directive at
 * http://www.php.net/manual/en/ref.errorfunc.php#ini.error-log
 *
 * Advice: Turn off debug mode in production environments because
 * it will eat up your ressources (especially disc space) and slow
 * down your system.
 */
// $options['debug'] = true;
$options['debug'] = false;

