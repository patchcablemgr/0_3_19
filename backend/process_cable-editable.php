<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('administrator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$cableID = $data['cableID'];
		$action = $data['action'];
		
		if($action == 'lock' or $action == 'unlock') {
			
			$editable = $action == 'lock' ? 0 : 1;
			
			$qls->SQL->update('app_inventory', array('editable' => $editable), array('id' => array('=', $cableID)));
			
		} else if($action == 'delete') {
			
			
			
			if(isset($qls->App->inventoryAllArray[$cableID])) {
				$cable = $qls->App->inventoryAllArray[$cableID];
				
				if($cable['a_object_id'] != 0 and $cable['b_object_id'] != 0) {
					
					$qls->SQL->delete('app_inventory', array('id' => array('=', $cableID)));
					
				} else {
					
					$errMsg = 'Unable to delete managed cables that are connected to ports.  Clear cable connection(s) and try again.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
			
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$actionArray = array('lock', 'unlock', 'delete');
	
	$validate->validateID($data['cableID'], 'cable ID');
	
	$validate->validateInArray($data['action'], $actionArray, 'editable action');
	
	return $error;
}

?>
