<?php

$path2 = array();

if($connectorCode39) {
	$connectorID2 = base_convert($connectorCode39, 36, 10);
	$rootCable2 = $qls->App->inventoryByIDArray[$connectorID2];
	
	$objID2 = $rootCable2['local_object_id'];
	$objFace2 = $rootCable2['local_object_face'];
	$objDepth2 = $rootCable2['local_object_depth'];
	$objPort2 = $rootCable2['local_object_port'];
}

$selectedObjID2 = $objID;
$selectedObjFace2 = $objFace;
$selectedObjDepth2 = $objDepth;
$selectedObjPort2 = $objPort;

$connSet = crawlConn($qls, $selectedObjID2, $selectedObjFace2, $selectedObjDepth2, $selectedObjPort2);
detectDivergence($connSet);

while(count($connSet[0]) or count($connSet[1])){
	
	$trunkSet = crawlTrunk($qls, $connSet);
	detectDivergence($trunkSet);
	
	foreach($trunkSet as $trunk) {
		foreach($trunk as $port) {
			
			$selectedObjID2 = $port['objID'];
			$selectedObjFace2 = $port['objFace'];
			$selectedObjDepth2 = $port['objDepth'];
			$selectedObjPort2 = $port['objDepth'];
			
			$workingConnSet = crawlConn($qls, $selectedObjID2, $selectedObjFace2, $selectedObjDepth2, $selectedObjPort2);
			detectDivergence($workingConnSet);
			
			foreach($workingConnSet as $workingConn) {
				
			}
		}
	}
}

error_log('Debug (connSet): '.json_encode($connSet));
error_log('Debug (trunkSet): '.json_encode($trunkSet));

function crawlTrunk(&$qls, $connSet) {
	
	$trunkSet = array(array(),array());
	
	// Loop over each side of $connSet
	foreach($connSet as $connSetID => $conn) {
		
		// Loop over each port of $conn
		foreach($conn as $connID => $port) {
			
			// Gather port data
			$objID = $port['objID'];
			$objFace = $port['objFace'];
			$objDepth = $port['objDepth'];
			$objPort = $port['objPort'];
			
			// Gather trunk peer data
			if(isset($qls->App->peerArray[$objID][$objFace][$objDepth])) {
				
				// Gather trunk peer object
				$peer = $qls->App->peerArray[$objID][$objFace][$objDepth];
				
				// Gather trunk peer data
				$peerObjID = $peer['peerID'];
				$peerObjFace = $peer['peerFace'];
				$peerObjDepth = $peer['peerDepth'];
				$peerObjPort = $objPort;
				
				// Create a working array for cleanliness
				$workingArray = array(
					'objID' => $peerObjID,
					'objFace' => $peerObjFace,
					'objDepth' => $peerObjDepth,
					'objPort' => $peerObjPort
				);
				
				// Store trunk data
				$trunkSet[$connSetID][$connID] = $workingArray;
			}
		}
	}
	
	return $trunkSet;
}

function crawlConn(&$qls, $objID, $objFace, $objDepth, $objPort, $connSetID=0, &$connSet=array(array(),array())) {
	
	// Store port details
	$workingArray = array(
		'objID' => $objID,
		'objFace' => $objFace,
		'objDepth' => $objDepth,
		'objPort' => $objPort
	);
	
	// Add port info to connection set
	array_push($connSet[$connSetID], $workingArray);
	
	// Is local port connected?
	if(isset($qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort])) {
		
		// Flip the connection set ID
		$connSetID = ($connSetID == 0) ? 1 : 0;
		
		// Loop over each local port connection
		$inventoryEntry = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort];
		foreach($inventoryEntry as $connection) {
			
			// Collect remote object data
			$remoteObjID = $connection['id'];
			$remoteObjFace = $connection['face'];
			$remoteObjDepth = $connection['depth'];
			$remoteObjPort = $connection['port'];
			
			// Verify this node has not been visited already
			$alreadySeen = false;
			foreach($connSet as $conn) {
				foreach($conn as $port) {
					if($port['objID'] == $remoteObjID and $port['objFace'] == $remoteObjFace and $port['objDepth'] == $remoteObjDepth and $port['objPort'] == $remoteObjPort) {
						$alreadySeen = true;
					}
				}
			}
			
			if(!$alreadySeen) {
				crawlConn($qls, $remoteObjID, $remoteObjFace, $remoteObjDepth, $remoteObjPort, $connSetID, $connSet);
			}
		}
	}
	
	return $connSet;
}

function detectDivergence(&$dataSet) {
	
	$pathDiverges = false;
	
	// Detect path divergence
	foreach($dataSet as &$data) {
		foreach($data as $portIndex => $port) {
			
			// Identify parent object ID
			$portObjID = $port['objID'];
			$portObj = $qls->App->objectArray[$portObjID];
			$portObjParentID = $portObj['parent_id'];
			while($portObjParentID != 0) {
				$portObj = $qls->App->objectArray[$portObjParentID];
				$portObjParentID = $portObj['parent_id'];
			}
			
			// Determine path divergence
			if($portIndex == 0) {
				$baselineParentID = $portObjParentID;
			} else {
				if($portObjParentID != $baselineParentID) {
					
					// Flag this path as divergent
					$pathDiverges = true;
					
					// Remove divergent connection
					unset($data[$portIndex]);
				}
			}
		}
	}
	unset($data);
	
	return $pathDiverges;
}

?>
