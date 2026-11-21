<?php
print 'Init...';
require_once(dirname(__DIR__) . '/include/config.php');
require_once(dirname(__DIR__) . '/include/autoload.php');
$db = new db();
print 'Done' . PHP_EOL;

$carddata = $bulkurl = $carddatapath = null;
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
		if(isset($list->object) && $list->object === 'bulk_data' && isset($list->type) && $list->type === 'default_cards' && isset($list->jsonl_download_uri) && !empty($list->jsonl_download_uri)){
			$bulkurl = $list->jsonl_download_uri;
			$date = date('Y-m-d H:i:s', strtotime($list->updated_at));
			$db->query('SELECT lastmodified FROM import LIMIT 1');
			$db->execute();
			if($db->rowCount() === 0){
				$db->query('INSERT INTO import (lastmodified) VALUES (:date)');
				$db->bind('date', $date);
				$db->execute();
			}else{
				$current = $db->fetch();
				$current = current($current);
				if($date <= $current){
					print 'Data hasn\'t been modified since last import, aborting' . PHP_EOL;
					exit();
				}else{
					$db->query('UPDATE import SET lastmodified = :date');
					$db->bind('date', $date);
					$db->execute();
					print 'Updated last modified date' . PHP_EOL;
				}
			}
			unset($response);
			print 'Found Bulk Data URL...';
			break;
		}
	}

	if(!empty($bulkurl)){
		$compressed = $inflate = false;
		print 'Downloading...';
		$ch = curl_init();
		$carddatapath = dirname(__FILE__) . '/' . basename($bulkurl);
		if(str_ends_with($carddatapath, '.gz') === true){
			$compressed = true;
			$carddatapath = substr($carddatapath, 0, -3);
			$inflate = inflate_init(ZLIB_ENCODING_GZIP);
		}
		$fp = fopen($carddatapath, 'w+');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $bulkurl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $scryfallheader);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, string $chunk) use ($inflate, $fp): int {
			$decoded = inflate_add($inflate, $chunk);
			if ($decoded === false) {
			    return 0;
			}

			fwrite($fp, $decoded);
			return strlen($chunk);
		});
		curl_exec($ch);
		curl_close($ch);
		print 'Done' . PHP_EOL;
	}
}else{
	print 'Failed' . PHP_EOL;
}

if(!empty($carddatapath)){
	$arenagametypes = $legalities = $oracleids = array();
	print 'Parsing Card Data...';
	$insert = $bindparams = array();
	$i = 0;
	$fp = fopen($carddatapath, 'r');
	while(($line = fgets($fp)) !== false){
		$line = trim($line);
		if($line === ''){
			continue;
		}
		$card = json_decode($line, true);
		if(!is_array($card)){
			continue;
		}
		$legal = false;
		$legality = 0;
		if(isset($card['object']) && $card['object'] === 'card' && isset($card['name']) && !empty($card['name']) && ((isset($card['games']) && is_array($card['games']) && in_array('arena', $card['games']))) && (!in_array($card['oracle_id'], $oracleids))){
			if(isset($card['legalities']) && !empty($card['legalities'])){
				foreach($card['legalities'] as $lkey => $lval){
					foreach(GAMEMODES as $gkey => $gamemode){;
						if($lkey === $gamemode && $lval === 'legal'){
							$legal = true;
							$legality ^= pow(2, $gkey);
						}
					}
				}
				if($legal === true){
					$oracleids[] = $card['oracle_id'];
					if(strpos($card['name'], '//') !== false && (!isset($card['layout']) || (isset($card['layout']) && $card['layout'] !== 'split'))){
						$card['name'] = explode('//', $card['name']);
						$card['name'] = current($card['name']);
						$card['name'] = trim($card['name']);
					}
					$bindparams['name' . $i] = $card['name'];
					$bindparams['alchemy' . $i] = ((isset($card['set_type']) && $card['set_type'] === 'alchemy') || (isset($card['promo_types']) && in_array('alchemy', $card['promo_types']))) ? 1 : 0;
					$bindparams['legal' . $i] = $legality;
					$insert[] = '(:name' . $i . ',:alchemy' . $i . ',:legal' . $i . ')';
					$i++;
				}
			}
		}
	}
	fclose($fp);
	print 'Done' . PHP_EOL;
	print 'Deleting Old Data...' ;
	$db->query('DELETE FROM cards');
	$db->execute();
	print 'Done' . PHP_EOL;
	print 'Inserting Data';
	$db->query('ALTER TABLE cards AUTO_INCREMENT = 1');
	$db->execute();
	$chunksize = 500;
	$insert = array_chunk($insert, $chunksize);
	$bindparams = array_chunk($bindparams, $chunksize * 3, true);
	foreach($insert as $chunkkey => &$insertdata){
		print '.';
		$sql = 'INSERT INTO cards (name,alchemy,legal) VALUES ' . implode(',', $insertdata) . ' ON DUPLICATE KEY UPDATE name=VALUES(name),alchemy=VALUES(alchemy),legal=VALUES(legal)';
		$db->query($sql);
		foreach($bindparams[$chunkkey] as $bindkey => $bindval){
			$db->bind($bindkey, $bindval);
		}
		$db->execute();
	}
	print 'Done' . PHP_EOL . 'Removing JSON File...';
	if(file_exists($carddatapath)){
		unlink($carddatapath);
	}
	print 'Done' . PHP_EOL;
}else{
	print 'Failed To Retrieve Card Data' . PHP_EOL;
}

?>