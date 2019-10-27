<!DOCTYPE html>
<?php
	header("Content-Type: text/html; charset=UTF-8");
    
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

    include_once('resources/fir.lib.inc.php');
    include_once('resources/api.lib.inc.php');

	$source_url = 'http://us.data.vatsim.net/vatsim-data.txt';
	$fetch_result = file_get_contents($source_url);
//var_dump($fetch_result);
	preg_match('/(?<=^\!CLIENTS\:\n)(.*)(?=^\;$\n^\;$)/msU', $fetch_result, $matches);

	
	$all_clientsRaw = explode(PHP_EOL, $matches[0]);
	
	//var_dump($all_clientsRaw);
	//var_dump($clientColumns);
	
	$all_atcs = []; $all_pilots = [];
	$online_atcs = []; $inbound_pilots = []; $outbound_pilots = [];
	foreach($all_clientsRaw as $clientRaw)
	{
		$all_client_infos = explode(':', $clientRaw);
		$newClient = [];
		foreach($clientColumns as $i => $column)
		{
			$newValue = $all_client_infos[$i];
			if(strlen($newValue) > 0)
			{
				$newClient[$column] = $newValue;
			}
		}
		$newClient['atis_message'] = urlencode($newClient['atis_message']);
		if($newClient['clienttype'] === "ATC")
		{
			$all_atcs[] = $newClient;
		}
		elseif($newClient['clienttype'] === "PILOT")
		{
			$all_pilots[] = $newClient;
		}
	}
	//var_dump($all_atcs);
	//var_dump($all_pilots);
	//echo "\n\n===================== ATCs ======================\n";
	foreach($all_atcs as $atc)
	{
		if(in_array($atc['callsign'], $firPositions))
		{
			//echo "\n".urldecode(json_encode($atc, JSON_PRETTY_PRINT))."\n";
			$online_atcs[] = $atc;
		}
	}
	//echo "\n\n==================== Pilots ====================\n";
	foreach($all_pilots as $pilot)
	{
		$pilot_dep = $pilot['planned_depairport'];
		$pilot_arr = $pilot['planned_destairport'];
		if(
			in_array($pilot_dep, array_keys(FirAirport::$all_list))
		)
		{
			//echo "\n".urldecode(json_encode($pilot, JSON_PRETTY_PRINT))."\n";
			$firArport = FirAirport::$all_list[$pilot_dep];
			$firArport->outbound_list[] = $pilot;
			$outbound_pilots[] = $pilot;
		}
		if(
			in_array($pilot_arr, array_keys(FirAirport::$all_list))
		)
		{
			//echo "\n".urldecode(json_encode($pilot, JSON_PRETTY_PRINT))."\n";
			$firArport = FirAirport::$all_list[$pilot_arr];
			$firArport->inbound_list[] = $pilot;
			$inbound_pilots[] = $pilot;
		}
	}
	//echo '<br><br>';
	//var_dump($online_atcs);
	//var_dump($inbound_pilots);
	//var_dump($outbound_pilots);
?>
<html>
<head>
	<meta charset="UTF-8">
	
	<style type="text/css">
		* {
			margin: 0 0;
			padding: 0 0;
		}
		body {
			font-family: "Helvetica", sans-serif;
			font-size: 14px;
			line-height: 16px;
			overflow: hidden;
		}
		.columnTo3 {
			display: inline-block;
			margin: 0 auto;
			padding: 0 0;
			width: 33%;
			height: 100vh;
		}
		.columnedTable {
			width: 100%;
		}
		table th,td {
			text-align: center;
			padding: 1px;
		}
		table th {
			color: white;
			background: rgba(95, 95, 95, 0.55);
		}
		table td {
			font-family: "Courier New", monospace;
		}
		table {
			color: rgb(75, 75, 75);
		}
	</style>
	
</head>
	<body>
		<div class="columnTo3">
			<table class="columnedTable">
				<thead>
					<tr>
						<th colspan="3">Contrôleurs en ligne</th>
					</tr>
					<tr>
						<th>Fréquence</th>
						<th>Indicatif</th>
						<th>Nom</th>
					</tr>
				</thead>
			<?php
				if(count($online_atcs) > 0)
				{
					foreach($online_atcs as $atc)
					{
						?>
						<tr>
							<td><?=$atc['frequency']?></td>
							<td><?=$atc['callsign']?></td>
							<td><?=$atc['realname']?><!-- (<?=$atc['rating']?>)--></td>
						</tr>
						<?php
					}
				}
				else
				{
					?>
					<tr>
						<td colspan="3">Aucun contrôleurs en ligne.</td>
					</tr>
					<?php
				}
			?>
			</table>
		</div>
		<div class="columnTo3">
			<table class="columnedTable">
				<thead>
					<tr>
						<th colspan="3">Arrivées</th>
					</tr>
					<tr>
						<th>Type</th>
						<th>Indicatif</th>
						<th>Provenance</th>
					</tr>
				</thead>
				
			<?php
				if(count($inbound_pilots) > 0)
				{
					foreach($inbound_pilots as $pilot)
					{
						?>
						<tr>
							<td><?=$pilot['planned_aircraft']?></td>
							<td><?=$pilot['callsign']?></td>
							<td><?=$pilot['planned_depairport']?>&nbsp;->&nbsp;<?=$pilot['planned_destairport']?></td>
						</tr>
						<?php
					}
				}
				else
				{
					?>
					<tr>
						<td colspan="3">Aucune arrivées.</td>
					</tr>
					<?php
				}
			?>
			</table>
		</div>
		<div class="columnTo3">
			<table class="columnedTable">
				<thead>
					<tr>
						<th colspan="3">Départs</th>
					</tr>
					<tr>
						<th>Type</th>
						<th>Indicatif</th>
						<th>Destination</th>
					</tr>
				</thead>
			<?php
				if(count($outbound_pilots) > 0)
				{
					foreach($outbound_pilots as $pilot)
					{
						?>
						<tr>
							<td><?=$pilot['planned_aircraft']?></td>
							<td><?=$pilot['callsign']?></td>
							<td><?=$pilot['planned_depairport']?>&nbsp;->&nbsp;<?=$pilot['planned_destairport']?></td>
						</tr>
						<?php
					}
				}
				else
				{
					?>
					<tr>
						<td colspan="3">Aucun départs.</td>
					</tr>
					<?php
				}
			?>
			</table>
		</div>
	</body>
</html>