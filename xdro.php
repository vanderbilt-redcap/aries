<?php
namespace Vanderbilt\XDRO;

class XDRO extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
	}
	
	// given a user supplied string, search for records in our patient registry that might match
	function patient_search($search_string) {
		// $name1, $name2, $date = "", "", "";
	}
}