<?php

require_once( '../class.MODEL.php' );

class localstore_model extends MODEL
{
	 function define_table()
        {
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( ! isset( $this->company_prefix ) )
			$this->tell_eventloop( $this, "SETTINGS_QUERY", 'company_prefix' );
		$tablename = "kallimachos_master";
                //The following should be common to pretty well EVERY table...
                $ind = "id_" . $tablename;
                $this->fields_array[] = array('name' => $ind, 'type' => 'int(11)', 'auto_increment' => 'yes', 'readwrite' => 'read' );
                $this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read' );
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

                $this->interestedin['NOTIFY_INSERT_UPC']['function'] = "insertUPC";
                $this->interestedin['NOTIFY_UPDATE_UPC']['function'] = "updateUPC";
                $this->interestedin['NOTIFY_DETAILS_HOLDING']['function'] = "details_holding";
                $this->interestedin['NOTIFY_DETAILS_SET_LOCAL']['function'] = "details_set_local";
                $this->interestedin['NOTIFY_SEARCH_LOCAL_UPC']['function'] = "searchUPC";
                $this->interestedin['NOTIFY_DELETE_HOLDING']['function'] = "delete_holding";
                $this->interestedin['SETTINGS_company_prefix']['function'] = "set_company_prefix";
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
        }
	function set_company_prefix( $obj, $msg )
	{
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->company_prefix( $val );
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	function company_prefix( $val )
	{
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->set( 'company_prefix', $val );
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	function details_holding( $obj, $msg )
	{
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
        	$this->delete_from_holding = TRUE;
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	function details_set_local( $obj, $msg )
	{
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
        	$this->in_master = TRUE;
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
        function insertUPC( $obj, $msg )
        {
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
                if( $this->in_master )
                {
                        $this->updateUPC( $obj, $msg );
                }
                else
                {
                        foreach( $msg as $key => $value )
                        {
                                $this->$key = $value;
                                $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "Setting master " . $key . " to " . $value, $this );
                        }
                        $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "Trying to update master", $this );
                        $this->InsertVAR();
                        $this->cleanup();
                }
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
        }
        function updateUPC( $obj, $msg )
        {
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
                foreach( $msg as $key => $value )
                {
                        $this->$key = $value;
                        $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "Setting master " . $key . " to " . $value, $this );
                }
                $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "Trying to update master", $this );
                $this->UpdateVAR();
                $this->cleanup();
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
        }
        function searchUPC( $obj, $msg )
        {
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
                if( isset( $obj->UPC ) )
                        $this->upc = $obj->UPC;
                else if( isset( $msg->UPC ) )
                        $this->upc = $msg->UPC;
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


        function searchUPC( $obj, $msg )
        {
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
                if( isset( $obj->UPC ) )
                        $this->upc = $obj->UPC;
                else if( isset( $msg->UPC ) )
                        $this->upc = $msg->UPC;
                $this->GetVARRow();
                if( isset( $this->Title ) )
                {
                        $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "UPC known locally", $this );
                        foreach( $this->fieldlist as $col )
                        {
                                if( strlen( $this->$col > 1 ) )
                                {
                                        $this->details[$col] = $this->$col;
                                        $this->ObserverNotify( 'NOTIFY_LOG_DEBUG', __FILE__ . ":" . __LINE__ . ":" .  "Setting $col to $this->$col", $this );
                                }
                                $this->details['comments'] = "holding " . $this->details['comments'];
                        }
                        $this->ObserverNotify( 'NOTIFY_DETAILS_SET', __FILE__ . ":" . __LINE__ . ":" .  "Details set", $this );
                        $this->ObserverNotify( 'NOTIFY_DETAILS_SET_LOCAL', __FILE__ . ":" . __LINE__ . ":" .  "Details set", $this );
                        $this->ObserverNotify( 'NOTIFY_DETAILS_HOLDING', __FILE__ . ":" . __LINE__ . ":" .  "Details stored in Holding table", $this );
                }
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
        }
        function delete_holding()
        {
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
                $this->deleteVAR();
                $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "Just tried to delete from holding.  Did it work?", $this );
                $this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
        }


}
