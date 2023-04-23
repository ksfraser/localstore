<?php

//require_once( MODELDIR . '/master.class.php' );
//require_once( MODELDIR . '/master_holding.class.php' );

require_once( dirname( __FILE__ ) . '/../../class.base.php' );


//class insertupdate_master extends master
class insertupdate_master extends base
{
	//This class is to take the data, and insert it into the Master database
	var $delete_from_holding;
	var $in_master;
	function __construct()
	{
		$this->ObserverRegister( $this, "NOTIFY_INSERT_UPC", 1 );
		$this->ObserverRegister( $this, "NOTIFY_UPDATE_UPC", 1 );
		$this->ObserverRegister( $this, "NOTIFY_DETAILS_HOLDING", 1 );
		$this->ObserverRegister( $this, "NOTIFY_DETAILS_SET_LOCAL", 1 );
		parent::__construct();
		$this->in_master = FALSE;
	}
	function notified( $obj, $event, $msg )
	{
		if( $event == "NOTIFY_INSERT_UPC" )
		{
			$this->insertUPC( $obj, $msg );
		}
		if( $event == "NOTIFY_UPDATE_UPC" )
		{
			$this->updateUPC( $obj, $msg );
		}
		if( $event == "NOTIFY_DETAILS_HOLDING" )
		{
			$this->delete_from_holding = TRUE;
		}
		if( $event == "NOTIFY_DETAILS_SET_LOCAL" )
		{
			$this->in_master = TRUE;
		}
	}
	function insertUPC( $obj, $msg )
	{
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
	}
	function updateUPC( $obj, $msg )
	{
                foreach( $msg as $key => $value )
                {
                        $this->$key = $value;
                        $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "Setting master " . $key . " to " . $value, $this );
                }
                $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "Trying to update master", $this );
                $this->UpdateVAR();
		$this->cleanup();
	}
	function cleanup()
	{
		if( $this->delete_from_holding )
		{
                	$this->ObserverNotify( 'NOTIFY_DELETE_HOLDING', __FILE__ . ":" . __LINE__ . ":" .  "Delete " . $this->upc, $this );
		}
	}
}

//class search_master extends master
class search_master extends base
{
	var $details = array();
	function __construct()
	{
		$this->ObserverRegister( $this, "NOTIFY_SEARCH_LOCAL_UPC", 1 );
		parent::__construct();
	}
	function notified( $obj, $event, $msg )
	{
		if( $event == "NOTIFY_SEARCH_LOCAL_UPC" )
		{
			$this->searchUPC( $obj, $msg );
		}
	}
	function searchUPC( $obj, $msg )
	{
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
	}
}

//class search_holding extends master_holding
class search_holding extends base
{
	var $details = array();
	var $details_already_set;
	function __construct()
	{
		$this->details_already_set = 0;
		$this->ObserverRegister( $this, "NOTIFY_SEARCH_LOCAL_UPC", 1 );
		$this->ObserverRegister( $this, "NOTIFY_DETAILS_SET_LOCAL", 1 );
		$this->ObserverRegister( $this, "NOTIFY_DELETE_HOLDING", 1 );
		parent::__construct();
	}
	function notified( $obj, $event, $msg )
	{
		if( $event == "NOTIFY_SEARCH_LOCAL_UPC" )
		{
			//DO we search holding if master has the details?
			if( $this->details_already_set == 0 )
			{
				$this->searchUPC( $obj, $msg );
			}
		}
		if( $event == "NOTIFY_DETAILS_SET_LOCAL" )
		{
			$this->details_already_set = 1;
		}
		if( $event == "NOTIFY_DELETE_HOLDING" )
		{
			$this->delete_holding();
		}
	}
	function searchUPC( $obj, $msg )
	{
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
	}
	function delete_holding()
	{
		$this->deleteVAR();
	        $this->ObserverNotify( 'NOTIFY_LOG_INFO', __FILE__ . ":" . __LINE__ . ":" .  "Just tried to delete from holding.  Did it work?", $this );
	}
}

?>
