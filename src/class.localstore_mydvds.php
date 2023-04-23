<?php

/*********************************************************
*
*       20110114 KF
*       Mydvds program downloads a complete DVD database
*       from the net.  Search its list
*| DVD Title       | text    | YES  |     | NULL    |       |
*| Studio          | text    | YES  |     | NULL    |       |
*| Released        | text    | YES  |     | NULL    |       |
*| Status          | text    | YES  |     | NULL    |       |
*| Sound           | text    | YES  |     | NULL    |       |
*| Versions        | text    | YES  |     | NULL    |       |
*| Price           | text    | YES  |     | NULL    |       |
*| Rating          | text    | YES  |     | NULL    |       |
*| Year            | text    | YES  |     | NULL    |       |
*| Genre           | text    | YES  |     | NULL    |       |
*| Aspect          | text    | YES  |     | NULL    |       |
*| UPC             | text    | YES  |     | NULL    |       |
*| DVD ReleaseDate | text    | YES  |     | NULL    |       |
*| ID              | int(11) | NO   | PRI |         |       |
*| Timestamp
*
**********************************************************/
require_once( '../class.MODEL.php' );

class search_mydvds extends MODEL
{
	var $upc;
	var $details = array();
// array('dsn' => 'mysql://kalliuser:kallipass@defiant.silverdart.no-ip.org/kalli');
	var $db_server;
	var $db_user;
	var $db_pass;
	var $db_database;
	var $dsn;
	var $data;
	function __construct()
	{
		$this->tell_eventloop( $this, 'SETTINGS_QUERY', 'db_server' );
		$this->tell_eventloop( $this, 'SETTINGS_QUERY', 'db_user' );
		$this->tell_eventloop( $this, 'SETTINGS_QUERY', 'db_pass' );
		$this->tell_eventloop( $this, 'SETTINGS_QUERY', 'db_database' );
		$this->tell_eventloop( $this, 'SETTINGS_QUERY', 'company_prefix' );
		parent::__construct();
	}
	function db_server( $val )
	{
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->set( 'db_server', $val );
	}
	function db_user( $val )
	{
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->set( 'db_user', $val );
	}
	function db_pass( $val )
	{
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->set( 'db_pass', $val );
	}
	function db_database( $val )
	{
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->set( 'db_database', $val );
	}
	function company_prefix( $val )
	{
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->set_company_prefix( $val );
	}
         function define_table()
        {
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
                if( ! isset( $this->company_prefix ) )
                        $this->tell_eventloop( $this, "SETTINGS_QUERY", 'company_prefix' );
                $tablename = "mydvds";
                //The following should be common to pretty well EVERY table...
                $ind = "id_" . $tablename;
                $this->fields_array[] = array('name' => $ind, 'type' => 'int(11)', 'auto_increment' => 'yes', 'readwrite' => 'read' );
                $this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read' );

		$this->fields_array[] = array('name' => 'DVD Title', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Studio', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Released', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Status', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Sound', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Versions', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Price', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Rating', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Year', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Genre', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Aspect', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'UPC', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'DVD ReleaseDate', 'type' => 'varchar(255)', 'null' => 'NULL', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'ID', 'type' => 'int(11)','readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'Timestamp', 'type' => 'timestamp', 'null' => 'NOT NULL','readwrite' => 'read' );

                $this->table_details['tablename'] = $this->company_prefix . $tablename;
                $this->table_details['primarykey'] = $ind;      //We can override this in child class!
                //$this->table_details['index'][0]['type'] = 'unique';
                //$this->table_details['index'][0]['columns'] = "variablename";
                //$this->table_details['index'][0]['keyname'] = "variablename";
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
        }
        function build_interestedin()
        {
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
                $this->interestedin['NOTIFY_INIT_TABLES']['function'] = "create_table";

                $this->interestedin['NOTIFY_DETAILS_SET_LOCAL']['function'] = "details_set_local";
                $this->interestedin['NOTIFY_SEARCH_LOCAL_UPC']['function'] = "searchUPC";
                $this->interestedin['NOTIFY_SEARCH_MYDVD']['function'] = "searchUPC";
                $this->interestedin['SETTINGS_company_prefix']['function'] = "set_company_prefix";
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
        }
	function set_company_prefix( $obj, $msg )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->set( 'company_prefix', $msg );	
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
  	function searchUPC( $obj, $msg )
        {
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");

                if( isset( $obj->UPC ) )
                        $this->upc = $obj->UPC;
                else if( is_object( $msg ) AND isset( $msg->UPC ) )
                        $this->upc = $msg->UPC;
		else if (is_string( $msg ) AND strlen( $msg ) < UPC_MAX_LEN )
			$this->upc = $msg;
		else
			throw new Exception( "Can't find a valid UPC to search for", KSF_VALUE_NOT_SET );


                $this->GetVARRow();
                if( isset( $this->Title ) and strlen( $this->Title ) > 1 )
                {
                        $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "UPC known locally", $this );
                        foreach( $this->fieldlist as $col )
                        {
                                if( strlen( $this->$col > 1 ) )
                                {
                                        $this->details[$col] = $this->$col;
                                        $this->ObserverNotify( 'NOTIFY_LOG_DEBUG', __FILE__ . ":" . __LINE__ . ":" .  "Setting $col to " . $this->$col, $this );
                                }
                                if( isset( $this->details['comments'] ) )
                                        $this->details['comments'] = "master " . $this->details['comments'];
                                else
                                        $this->details['comments'] = "master ";
                        }
                        $this->ObserverNotify( 'NOTIFY_DETAILS_SET_LOCAL', __FILE__ . ":" . __LINE__ . ":" .  "Details set", $this );
                        $this->ObserverNotify( 'NOTIFY_DETAILS_SET', __FILE__ . ":" . __LINE__ . ":" .  "Details set", $this );
                }
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
        }



	function set_db_server( $server = "defiant.ksfraser.com" )
	{
		$this->db_server = $server;
	}
	function set_db_user( $user = "kalliuser" )
	{
		$this->db_user = $user;
	}
	function set_db_pass( $pass = "kallipass" )
	{
		$this->db_pass = $pass;
	}
	function set_db_database( $database = "kalli" )
	{
		$this->db_database = $database;
	}
	/********************************//**
	* TODO Make this not MySQL specific!!
	*
	* @param none
	* @returns none
	************************************/
	function set_dsn()
	{
		if( !isset( $this->db_user ) )
			throw new Exception( "DB User not set", KSF_VALUE_NOT_SET );
		if( !isset( $this->db_pass ) )
			throw new Exception( "DB Pass not set", KSF_VALUE_NOT_SET );
		if( !isset( $this->db_database ) )
			throw new Exception( "DB database not set", KSF_VALUE_NOT_SET );
		if( !isset( $this->db_server ) )
			throw new Exception( "DB server not set", KSF_VALUE_NOT_SET );

		$this->dsn = "mysql://" . $this->db_user . ":" . $this->db_pass . "@" . $this->db_server . "/" . $this->db_database;
	}
	function searchUPC( $obj, $msg )
	{
                if( isset( $obj->UPC ) )
                        $this->upc = $obj->UPC;
                else if( isset( $msg->UPC ) )
                        $this->upc = $msg->UPC;
		
		$this->getMydvds();
		if( isset( $this->data['DVD_Title'] ) )
		{
	                $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "UPC known locally", $this );
			$this->MyDVD2details();
	                $this->ObserverNotify( 'NOTIFY_DETAILS_SET_LOCAL', __FILE__ . ":" . __LINE__ . ":" .  "Details set" , $this);
	                $this->ObserverNotify( 'NOTIFY_DETAILS_SET', __FILE__ . ":" . __LINE__ . ":" .  "Details set", $this );
		}
		return;
	}
	function getMydvds()
	{
	//Return an array of data
	        require 'Structures/DataGrid.php';
	        require_once 'HTML/Table.php';
	
	        // Instantiate the DataGrid
	        $datagrid =& new Structures_DataGrid(10);
	
	        // Setup your database connection
		$this->set_dsn();
	        $dboptions = array('dsn' => $this->dsn);
	
	        // Bind a basic SQL statement as datasource
	        $test = $datagrid->bind("SELECT * FROM mydvds_Region1 where UPC = '" . $this->upc . "'", $dboptions);
	
	        // Print binding error if any
	        if (PEAR::isError($test)) {
	            echo $test->getMessage();
	        }
	
	        //var_dump( $datagrid->recordSet[0] );
	        if( isset( $datagrid->recordSet[0] ))
	        {
	                $this->data = $datagrid->recordSet[0];
	        }
	        else
	        {
	                $this->data = array();
	        }
		return;
	}
	function data2kalli_data()
	{
		$kd = new kalli_data();
		foreach( $this->conversion_array as $row )
		{
			if( isset( $this->data[ $row['source'] ] ) )
			{
				$kd->set( $row['dest'], $this->data[ $row['source'] ] );
			}
		}
		$kd->finish();
		return $kd;
	}
	function MyDVD2details()
	{
		$this->conversion_array = array (
	                array ( 'dest' =>'Title', 'source' => 'DVD_Title' ),
	                array ( 'dest' =>'Genre', 'source' => 'Genre' ),
	                array ( 'dest' =>'year', 'source' => 'Released' ),
	                array ( 'dest' =>'isbn', 'source' => 'UPC' ),
	                array ( 'dest' =>'publisher', 'source' => 'Studio' ),
	                array ( 'dest' =>'mpaarating', 'source' => 'Rating' ),
	                array ( 'dest' =>'releasedate', 'source' => 'DVD ReleaseDate' ) );
	        return $this->data2kalli_data();
	}

}

?>
