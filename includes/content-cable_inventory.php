<?php
	require_once './includes/shared_tables.php';
	
	$pillYes = '<span class="label label-pill label-success">Yes</span>';
	$pillNo = '<span class="label label-pill label-danger">No</span>';
	
	echo '<table id="inventoryTable" class="table table-striped table-bordered">';
	echo '<thead>';
		echo '<tr>';
			echo '<th colspan="3" style="text-align:center">Cable End A</th>';
			echo '<th colspan="3" style="text-align:center">Cable End B</th>';
			echo '<th colspan="3" style="text-align:center">Cable Properties</th>';
		echo '</tr>';
		echo '<tr>';
			echo '<th>ID</th>';
			echo '<th>Connector</th>';
			echo '<th>Connected</th>';
			echo '<th>ID</th>';
			echo '<th>Connector</th>';
			echo '<th>Connected</th>';
			echo '<th>Media</th>';
			echo '<th>Length</th>';
			echo '<th>Action</th>';
		echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	$query = $qls->SQL->select('*', 'app_inventory', array('active' => array('=', 1)));
	while($row = $qls->SQL->fetch_assoc($query)) {
		$mediaTypeID = $row['mediaType'];
		$lengthValue = $row['length'];
		$length = $qls->App->calculateCableLength($mediaTypeID, $lengthValue);
		
		echo '<tr id="'.$row['id'].'">';
			echo '<td data-connectorID="'.$row['a_code39'].'"><a class="linkScan" href="#">'.$row['a_code39'].'</a><button class="displayBarcode pull-right btn btn-sm waves-effect waves-light btn-primary"><i class="fa fa-barcode"></i></button></td>';
			echo '<td>'.$connectorTable[$row['a_connector']]['name'].'</td>';
			if($row['a_object_id'] == 0) {
				echo '<td>'.$pillNo.'</td>';
			} else {
				echo '<td>'.$pillYes.'</td>';
			}
			echo '<td data-connectorID="'.$row['b_code39'].'"><a class="linkScan" href="#">'.$row['b_code39'].'</a><button class="displayBarcode pull-right btn btn-sm waves-effect waves-light btn-primary"><i class="fa fa-barcode"></i></button></td>';
			echo '<td>'.$connectorTable[$row['b_connector']]['name'].'</td>';
			if($row['b_object_id'] == 0) {
				echo '<td>'.$pillNo.'</td>';
			} else {
				echo '<td>'.$pillYes.'</td>';
			}
			echo '<td>'.$mediaTypeTable[$row['mediaType']]['name'].'</td>';
			echo '<td>'.$length.'</td>';
			echo '<td>';
			if($row['editable'] == 0) {
				echo '<a title="Unlock cable for editing" class="linkEditable" data-action="unlock" data-cableID="'.$row['id'].'" href="javascript:void(0);">';
				echo '<i class="fa fa-lock"></i>';
				echo '</a>';
			} else {
				echo '<a title="Lock cable for editing" class="linkEditable" data-action="lock" data-cableID="'.$row['id'].'" href="javascript:void(0);">';
				echo '<i class="fa fa-unlock"></i>';
				echo '</a>';
			}
			echo '&nbsp&nbsp';
			echo '<a title="Delete cable" class="linkEditable" data-action="delete" data-cableID="'.$row['id'].'" href="javascript:void(0);" data-toggle="modal" data-target="#modalConfirm">';
			echo '<i class="fa fa-times"></i>';
			echo '</a>';
			echo '</td>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
?>
