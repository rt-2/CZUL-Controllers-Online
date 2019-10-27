<?php
class FirAirport
{
		
	public $icao = '';
	public $name = '';
	public $inbound_list = [];
	public $outbound_list = [];
		
	static $all_list = [];
		
	public function __construct($icao, $name)
	{
		$this->icao = $icao;
		$this->name = $name;
		self::$all_list[$icao] = $this;
	}
		
}
?>