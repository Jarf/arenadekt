<?php
print 'Init...';
require_once(dirname(__DIR__) . '/include/config.php');
require_once(dirname(__DIR__) . '/include/autoload.php');

$download = !ISDEV;
print 'Done' . PHP_EOL;

$carddata = $bulkurl = null;
if($download){
	print 'Downloading Bulk Data Listing...';
	$ch = curl_init();
	$scryfallheader = array('User-Agent: arenadekt','Accept: application/json');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, 'https://api.scryfall.com/bulk-data');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $scryfallheader);
	$response = curl_exec($ch);
	$response = @json_decode($response);
	curl_close($ch);
	if(!empty($response) && isset($response->data)){
		print 'Done' . PHP_EOL;
		foreach($response->data as &$list){
			if(isset($list->object) && $list->object === 'bulk_data' && isset($list->type) && $list->type === 'oracle_cards' && isset($list->download_uri) && !empty($list->download_uri)){
				$bulkurl = $list->download_uri;
				print 'Found Bulk Data URL...';
				break;
			}
		}

		if(!empty($bulkurl)){
			print 'Downloading...';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $bulkurl);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $scryfallheader);
			$carddata = curl_exec($ch);
			$carddata = @json_decode($carddata);
			curl_close($ch);
			print 'Done' . PHP_EOL;
		}
	}else{
		print 'Failed' . PHP_EOL;
	}
}else{
	$carddata = file_get_contents(dirname(__FILE__) . '/oracle-cards-20241029090205.json');
	$carddata = @json_decode($carddata);
}

if(!empty($carddata)){
	print 'Parsing Card Data';
	$db = new db();
	$insert = $bindparams = array();
	$i = 0;
	foreach($carddata as &$card){
		if(
			isset($card->object) && $card->object === 'card' && isset($card->name) && !empty($card->name) && (
				(isset($card->games) && is_array($card->games) && in_array('arena', $card->games)) || 
				(isset($card->legalities) && 
					(isset($card->legalities->alchemy) && $card->legalities->alchemy === 'legal') ||
					(isset($card->legalities->brawl) && $card->legalities->brawl === 'legal') ||
					(isset($card->legalities->historic) && $card->legalities->historic === 'legal')
				)
			)
		){
			$bindparams['name' . $i] = $card->name;
			$bindparams['alchemy' . $i] = ((isset($card->set_type) && $card->set_type === 'alchemy') || (isset($card->promo_types) && in_array('alchemy', $card->promo_types))) ? 1 : 0;
			$insert[] = '(:name' . $i . ',:alchemy' . $i . ')';
			$i++;
		}
		print '.';
	}
	print 'Done' . PHP_EOL;
	print 'Inserting Data';
	$chunksize = 500;
	$insert = array_chunk($insert, $chunksize);
	$bindparams = array_chunk($bindparams, $chunksize * 2, true);
	foreach($insert as $chunkkey => $insertdata){
		print '.';
		$sql = 'INSERT INTO cards (name,alchemy) VALUES ' . implode(',', $insertdata) . ' ON DUPLICATE KEY UPDATE name=VALUES(name),alchemy=VALUES(alchemy)';
		$db->query($sql);
		foreach($bindparams[$chunkkey] as $bindkey => $bindval){
			$db->bind($bindkey, $bindval);
		}
		$db->execute();
	}
	print 'Done' . PHP_EOL;
}else{
	print 'Failed To Retrieve Card Data' . PHP_EOL;
}

?>