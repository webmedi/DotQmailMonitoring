<?php

spl_autoload_register( function ( $class ) {
	require( __DIR__ . DIRECTORY_SEPARATOR . $class . '.php' );

});

$myDotQmailMonitoring = new DotQmailMonitoring( );

?>