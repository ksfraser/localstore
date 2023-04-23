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

require_once( 'data/generictable.php' );
class search_mydvds extends generictable
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
		$this->ObserverRegister( $this, "NOTIFY_SEARCH_LOCAL_UPC", 1 );
		$this->ObserverRegister( $this, "NOTIFY_SEARCH_MYDVD", 1 );
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
	function set_dsn()
	{
		if( !isset( $this->db_user ) )
			$this->set_db_user();
		if( !isset( $this->db_pass ) )
			$this->set_db_pass();
		if( !isset( $this->db_database ) )
			$this->set_db_database();
		if( !isset( $this->db_server ) )
			$this->set_db_server();

		$this->dsn = "mysql://" . $this->db_user . ":" . $this->db_pass . "@" . $this->db_server . "/" . $this->db_database;
	}
	function notified( $obj, $event, $msg )
	{
		if( $event == "NOTIFY_SEARCH_LOCAL_UPC" )
		{
			$this->searchUPC( $obj, $msg );
		}
		if( $event == "NOTIFY_SEARCH_MYDVD" )
		{
			$this->searchUPC( $obj );
		}
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
	function MyDVD2details()
	{
	                if( isset( $this->data['DVD_Title'] ))
	                        $this->details['Title'] = $this->data['DVD_Title'];
	                if( isset( $this->data['Genre'] ))
	                        $this->details['Genre'] = $this->data['Genre'];
	                if( isset( $this->data['Released'] ))
	                        $this->details['year'] = $this->data['Released'];
	                if( isset( $this->data['UPC'] ))
	                        $this->details['isbn'] = $this->data['UPC'];
	                if( isset( $this->data['Studio'] ))
	                        $this->details['publisher'] = $this->data['Studio'];
	                if( isset( $this->data['Rating'] ))
	                        $this->details['mpaarating'] = $this->data['Rating'];
	                if( isset( $this->data['DVD_ReleaseDate'] ))
	                        $this->details['releasedate'] = $this->data['DVD ReleaseDate'];
	        return;
	}

}

?>
