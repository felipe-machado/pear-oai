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
	
	$Id: index.php,v 1.1.1.1 2003/06/30 11:50:07 ordnas Exp $
*/

// include the file with the general PHP environment
// configuration
// please check this file and adjust the parameters
// to your needs
require_once 'config/phpconfig.php';

?>
<html>
<head>
  <title>Meertens Institute Metadata Repository</title>
</head>
<body>
<h1>Meertens Institute Metadata Repository</h1>

<h2>Identify</h2>

<p><b>Test URL: </b><a href="oai_server.php?verb=Identify">oai_server.php?verb=Identify</a></p>

<h2>ListMetadataFormats</h2>

<p><b>Test URL: </b><a href="oai_server.php?verb=ListMetadataFormats">oai_server.php?verb=ListMetadataFormats</a></p>

<h2>ListSets</h2>

<p><b>Test URL: </b><a href="oai_server.php?verb=ListSets">oai_server.php?verb=ListSets</a></p>

<h2>ListIdentifiers</h2>

<p><b>oai_dc: </b><a href="oai_server.php?verb=ListIdentifiers&metadataPrefix=oai_dc">oai_server.php?verb=ListIdentifiers&metadataPrefix=oai_dc</a><br />
  <b>cmdi: </b><a href="oai_server.php?verb=ListIdentifiers&metadataPrefix=cmdi">oai_server.php?verb=ListIdentifiers&metadataPrefix=cmdi</a>
</p>

<h3>ListIdentifiers with date range</h3>
<p><a href="oai_server.php?verb=ListIdentifiers&metadataPrefix=cmdi&from=2011-06-28T16:33:36Z&until=2011-06-28T16:33:49Z">oai_server.php?verb=ListIdentifiers&metadataPrefix=cmdi&from=2011-06-28T16:33:36Z&until=2011-06-28T16:33:49Z</a></p>

<h2>ListRecords</h2>

<p><b>oai_dc: </b><a href="oai_server.php?verb=ListRecords&metadataPrefix=oai_dc">oai_server.php?verb=ListRecords&metadataPrefix=oai_dc</a><br />
  <b>cmdi: </b><a href="oai_server.php?verb=ListRecords&metadataPrefix=cmdi">oai_server.php?verb=ListRecords&metadataPrefix=cmdi</a>
</p>

<?php

$dc_identifier = urlencode('some_dc_identifier');
$cmdi_identifier = urlencode('some_cmdi_identifier');

?>

<h2>GetRecord</h2>

<p><b>oai_dc: </b><a
    href="oai_server.php?verb=GetRecord&identifier=<?php echo $dc_identifier; ?>&metadataPrefix=oai_dc">oai_server.php?verb=GetRecord&identifier=<?php echo $dc_identifier; ?>
    &metadataPrefix=oai_dc</a><br />
  <b>cmdi: </b><a href="oai_server.php?verb=GetRecord&identifier=<?php echo $cmdi_identifier; ?>&metadataPrefix=cmdi">oai_server.php?verb=GetRecord&identifier=<?php echo $cmdi_identifier; ?>
    &metadataPrefix=cmdi</a></p>


</body>
</html>