<?php
class convertor{
	private $db;

	public function __construct(){
		$this->db = new db();
	}

	public function convertArchidektToArena(string $archidektdata){
		$result = array(
			'arenadeck' => array(),
			'removed' => array(),
			'replaced' => array(),
			'info' => array(),
			'removedcount' => 0,
			'replacedcount' => 0
		);
		$deck = $decknamemap = $bindparams = $where = $arena = array();
		$archidektdata = explode(PHP_EOL, $archidektdata);
		$archidektdata = array_filter($archidektdata);
		foreach($archidektdata as $ckey => &$card){
			$card = trim($card);
			if(strpos($card, '//') !== false){
				$card = explode('//', $card);
				$card = current($card);
				$card = trim($card);
			}
			if(preg_match('/^(\d)\s(.*)$/', $card, $matches)){
				$count = intval($matches[1]);
				$name = trim($matches[2]);
				if(!in_array($name, $bindparams)){
					$bindparams['name' . $ckey] = $name;
					$where[] = ':name' . $ckey;
					$deck[$ckey] = array(
						'name' => $name,
						'count' => $count
					);
					$decknamemap[$name] = $ckey;
				}else{
					$deck[$decknamemap[$name]]['count']++;
				}
			}
		}
		if(!empty($where)){
			$sql = 'SELECT name FROM cards WHERE name IN (' . implode(',', $where) . ')';
			$this->db->query($sql);
			foreach($bindparams as $bkey => $bval){
				$this->db->bind($bkey, $bval, PDO::PARAM_STR);
			}
			$this->db->execute();
			$rs = $this->db->fetchAll();
			foreach($rs as $row){
				$arena[] = $deck[$decknamemap[$row->name]];
			}
			$notfound = array_map('json_decode',array_diff(array_map('json_encode', $deck), array_map('json_encode', $arena)));
			$where = $bindparams = array();
			if(!empty($notfound)){
				// Check not found for alchemy equivalents
				$i = 0;
				foreach($notfound as $nfkey => $nfcard){
					if(substr($nfcard->name, 0, 2) !== 'A-'){
						$where[] = 'name = :name' . $i;
						$bindparams['name'. $i] = 'A-' . $nfcard->name;
						$i++;
					}
				}
				if(!empty($where)){
					$sql = 'SELECT name FROM cards WHERE ' . implode(' OR ', $where);
					$this->db->query($sql);
					foreach($bindparams as $bkey => $bval){
						$this->db->bind($bkey, $bval, PDO::PARAM_STR);
					}
					$this->db->execute();
					$rs = $this->db->fetchAll();
					if(!empty($rs)){
						foreach($rs as $row){
							foreach($notfound as $nfkey => $nfcard){
								if($nfcard->name === substr($row->name, 2)){
									unset($notfound[$nfkey]);
									$arena[] = array(
										'name' => $row->name,
										'count' => $nfcard->count
									);
									$result['replaced'][] = $nfcard->count . ' ' . $nfcard->name;
									break;
								}
							}
						}
					}
				}
				foreach($notfound as $nfcard){
					$result['removed'][] = $nfcard->count . ' ' . $nfcard->name;
				}
			}
		}else{
			$result['info'][] = 'Unable to parse deck list, either your deck has no cards in Arena or you didn\'t follow the instructions';
		}
		foreach($arena as $fcard){
			$result['arenadeck'][] = $fcard['count'] . ' ' . $fcard['name'];
		}
		$result['removedcount'] = count($result['removed']);
		$result['replacedcount'] = count($result['replaced']);
		foreach($result as $rkey => $rval){
			if(is_array($result[$rkey])){
				$result[$rkey] = implode(PHP_EOL, $rval);
			}
		}
		return $result;
	}
}
?>