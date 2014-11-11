<?php

// Set options for storage driver of our repository data backend.
// In our case, it's a RDBMS
//
// Please check out the respective backend plugin documentation for available options

$storage_options = array(
// the table name where we store repository data
'table' => 'oai',
// if we store multiple entries for one element
// in a single row of our repository table, 'element_delimiter'
// ist the delimiter for these entries.
// If you do not do this, do not define 'delimiter'
'delimiter' => ';',
// the name of the column where you store your datestamps
'datestamp' => 'oai_datestamp',
// the name of the column where you store information whether
// a record has been deleted. Leave it as it is if you do not use
// this feature.
'deleted' => 'oai_deleted',
// the name of the column where you store sets information of the record
'sets' => 'oai_sets',
// the name of the column where we store the item identifier,
// or, sequence, or ID (= autoincremnt values).
// This is different from the item identifier. For more information,
// please consult
// http://www.openarchives.org/OAI/openarchivesprotocol.html#UniqueIdentifier
'identifier' => 'dc_identifier'
);

$storage_options['templates_path'] = $storage_options['template_path'] = realpath('OAI/Server/Output/');

// PEAR::DB DSN, see http://pear.php.net/manual/en/package.database.db.intro-dsn.php
define('OAI_STORAGE_DSN', '');
define('OAI_TOKENSTORAGE_DSN', '');