<?php

class localdatagrid extends origin
{
	protected $dbhost;
	protected $dbuser;
	protected $dbpass;
	protected $dbdb;
	protected $dbtable;
	protected $conversion_array;
	protected $ini;
	protected $queryval;

	function __construct()
	{
		
		parent::__construct();
		if( ! isset( $this->ini ) )
			$this->ini = "/../../localdatagrid.ini";
		$this->tell_eventloop( $this, "READ_INI", dirname( __FILE__ ) . $this->ini );
		$this->tell_eventloop( $this, "SETTINGS_QUERY", 'dbhost' );
		$this->tell_eventloop( $this, "SETTINGS_QUERY", 'dbuser' );
		$this->tell_eventloop( $this, "SETTINGS_QUERY", 'dbpass' );
		$this->tell_eventloop( $this, "SETTINGS_QUERY", 'dbdb' );
		$this->tell_eventloop( $this, "SETTINGS_QUERY", 'dbtable' );
		$this->conversion_array = array();
	}
	/*********************************************//**
	 * Build the array used by kalli_data copy_from_source
	 *
	 * INHERITING classes MUST OVERRIDE!!
	 * @param none
	 * @return null
	 * ***********************************************/
	function build_conversion_array()
	{
		throw new Exception( "Inheriting class must override!" );
	}
	/*****************************************************//**
        * SETTINGS_QUERY handler checks for fcn to set value based upon query
        *
        * @param val the Returned setting value
        * @return NONE
        ********************************************************/
        function dbhost( $val )
        {
                $this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', 'Setting dbhost to ' . $val );
                $this->set( 'dbhost', $val );
        }
	/*****************************************************//**
        * SETTINGS_QUERY handler checks for fcn to set value based upon query
        *
        * @param val the Returned setting value
        * @return NONE
        ********************************************************/
        function dbuser( $val )
        {
                $this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', 'Setting dbuser to ' . $val );
                $this->set( 'dbuser', $val );
        }
	/*****************************************************//**
        * SETTINGS_QUERY handler checks for fcn to set value based upon query
        *
        * @param val the Returned setting value
        * @return NONE
        ********************************************************/
        function dbpass( $val )
        {
                $this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', 'Setting dbpass to ' . $val );
                $this->set( 'dbpass', $val );
        }
	/*****************************************************//**
        * SETTINGS_QUERY handler checks for fcn to set value based upon query
        *
        * @param val the Returned setting value
        * @return NONE
        ********************************************************/
        function dbdb( $val )
        {
                $this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', 'Setting dbdb to ' . $val );
                $this->set( 'dbdb', $val );
	}
	/*****************************************************//**
        * SETTINGS_QUERY handler checks for fcn to set value based upon query
        *
        * @param val the Returned setting value
        * @return NONE
        ********************************************************/
        function dbtable( $val )
        {
                $this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', 'Setting dbtable to ' . $val );
                $this->set( 'dbtable', $val );
        }
	/***************************************************************//**
         *build_interestedin
         *
         *      This function builds the table of events that we
         *      want to react to and what handlers we are passing the
         *      data to so we can react.
         * ******************************************************************/
        function build_interestedin()
        {
                $this->interestedin["SEEK_ISBN"]['function'] = "seek_UPC";
                $this->interestedin["SEEK_UPC"]['function'] = "seek_UPC";
                $this->interestedin["NOTIFY_SEARCH_LOCAL_UPC"]['function'] = "seek_UPC";	
        }
        /******************************************************//**
	 * Search for a UPC
	 *
	 * Requires the UPC class.
        *
        * @param caller
        * @param data string the UPC to search for
        * @returns bool 
        ********************************************************/
        function seek_UPC( $caller, $data )
        {
   		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', get_class( $this ) . "::" . __FUNCTION__ . "::" . __LINE__ );
		require_once( '../class.UPC.php' );
		$upc = new UPC();
		$ret = $upc->setUPC( $caller, $data );
		if( $ret )
		{
   			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', "Set UPC" );
			$this->set( 'queryval', $upc->get( 'UPC' ) );
                	$ret = $this->run();
                	//if( $ret )
                	//	$this->tell_eventloop( $this, 'NOTIFY_RETURN', $ret );
			//var_dump( $ret );
                	return $ret;
		}
		else
			return FALSE;
	}
	/**************************************************//**
	 * Query a database for data on our UPC.
	 *
	 * @param none
	 * @return bool
	 * ****************************************************/
	function run()
	{
		if( !isset( $this->dbuser ) )
			throw new Exception( "DB User not set", KSF_VALUE_NOT_SET );
		if( !isset( $this->dbpass ) )
			throw new Exception( "DB Pass not set", KSF_VALUE_NOT_SET );
		if( !isset( $this->dbdb ) )
			throw new Exception( "DB database not set", KSF_VALUE_NOT_SET );
		if( !isset( $this->dbhost ) )
			throw new Exception( "DB server not set", KSF_VALUE_NOT_SET );

		//Return an array of data
		require_once 'Structures/DataGrid.php';
		require_once 'HTML/Table.php';
	
		// Instantiate the DataGrid
		$datagrid = new Structures_DataGrid(10);
		//$datagrid =& new Structures_DataGrid(10);
		// Setup your database connection
		// 	'mysql://kalliuser:kallipass@defiant.silverdart.no-ip.org/kalli'
		$dsn = 'mysql://' . $this->dbuser . ":" . $this->dbpass . "@" . $this->dbhost . "/" . $this->dbdb;
		$dboptions = array('dsn' => $dsn );
		//"SELECT * FROM mydvds_Region1 where UPC = '$UPC'"
		$query = "SELECT * FROM " . $this->dbtable . " where UPC = '" . $this->queryval . "'";
	
		// Bind a basic SQL statement as datasource
		$test = $datagrid->bind( $query, $dboptions);		
		// Print binding error if any
		if (PEAR::isError($test)) {
			$this->tell_eventloop( $this, 'NOTIFY_LOG_ERROR', get_class( $this ) . "::" . __FUNCTION__ . "::" . __LINE__ . "::" . $test->getMessage() );
		}
		if( isset( $datagrid->recordSet[0] ))
		{
			//$test = $datagrid->render('CSV');
			//var_dump( $test );
			//$test = $datagrid->render('Excel');
			//$test = $datagrid->render('Console');
			var_dump( $datagrid );
			$this->datagrid2var( $datagrid );
			$this->build_conversion_array();
			$this->tell_eventloop( $this, 'NOTIFY_DATA_FOR_COPY', $this->conversion_array );
			return TRUE;
		}
		return FALSE;
	}
	/******************************************//**
	 * Extract returned datagrid values into our variables
	 *
	 * @param datagrid object structures_datagrid from PEAR
	 * @return null
	 * *********************************************/
	function datagrid2var( $datagrid )
	{
		throw new Exception( "Inheriting Class MUST Override!" );
	}
}





//Testing
//var_dump( getMydvds( $argv[1] ) );
?> 
