<?xml version="1.0" encoding="UTF-8"?>

<!-- BEGIN ENVELOPE -->
<OAI-PMH	xmlns="{OAI_NAMESPACE}"
			xmlns:xsi="{OAI_XSD_INSTANCE}"
			xsi:schemaLocation="{OAI_NAMESPACE} {OAI_SCHEMA_LOCATION}">
	<responseDate>{RESPONSE_DATE}</responseDate>
	<request{REQUEST_ATTRIBUTES}>{REQUEST_URL}</request>
	
	<!-- BEGIN ERROR -->
	<error code="{ERROR_CODE}">{ERROR_DESCRIPTION}</error>
	<!-- END ERROR -->
	
	{CONTENT}
	
</OAI-PMH>
<!-- END ENVELOPE -->
