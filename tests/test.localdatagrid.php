<?php

session_start();

global $eventloop;

require_once( '../class.origin.php' );
require_once( '../ksf_settings/class.ksf_settings.php' );
require_once( '../ksf_settings/class.ksf_ini.php' );
require_once( '../Log/class.Log.php' );

global $ini;
$ini = new ksf_ini();
$settings = new ksf_settings();
$l = new Log();

$ini->register_with_eventloop();
$settings->register_with_eventloop();
//$l->register_with_eventloop();

$ini->read_ini( null, "../../localdatagrid.ini" );

//var_dump( $_SESSION );

require_once( '../kalli_data/class.kalli_data.php' );
$kd = new kalli_data();

require_once( 'class.localdatagrid.php' );
$isbn = new localdatagrid( );
$isbn->tell_eventloop( $isbn, "SEEK_UPC", $argv[1]  );
//var_dump( $isbn );
var_dump( $kd );

