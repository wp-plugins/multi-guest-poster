<?php
require_once(dirname(__FILE__) . '/articlebase.class.php');
	
class artigonal extends articlebase {

	public function __construct( $keyword = '', $limit = 0) {
		parent::__construct($keyword, $limit);
		$this->domain = 'www.artigonal.com';
	}
	
}

?>