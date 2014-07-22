<?php

class TTTCrop_Common {
	const sname = 'tttcrop';

	public function __construct() {
		$s = load_plugin_textdomain( self::sname, false, TTTINC_CROP . '/lang/' );
	}

}

?>
