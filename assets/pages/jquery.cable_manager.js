/**
 * Order
 * This page takes in order items, shipping address, and payment info
 */

$( document ).ready(function() {
	$('#inventoryTable').DataTable();
	$('#availableCableEndIDTable').DataTable({'ordering': false, 'searching': false});
	
	$('.linkScan').on('click', function(e){
		e.preventDefault();
		var code39 = $(this).parent().attr('data-connectorID');
		$(location).attr('href', '/scan.php?connectorCode='+code39);
	});
	
	$('.displayBarcode').on('click', function(){
		var tableCell = $(this).parent();
		var connectorID = $(tableCell).attr('data-connectorID');
		
		$(this).siblings().hide();
		$(this).hide();
		
		$(tableCell).append('<div id="barcodeContainer'+connectorID+'"></div>');
		$('#barcodeContainer'+connectorID).barcode(connectorID, "code39", {barWidth:2}).on('click', function(){
			$(this).siblings().show();
			$(this).remove();
		});
	});
	
	$('.linkEditable').on('click', function(){
		var linkEditable = $(this);
		var action = $(linkEditable).attr('data-action');
		var cableID = $(linkEditable).attr('data-cableID');
		
		if(action == 'delete') {
			
			$(document).data('selectedCableID', cableID);
			
			$('#modalConfirmTitle').html('Delete Cable');
			$('#modalConfirmBody').html('Delete cable?');
			
		} else {
			var data = {
				cableID: cableID,
				action: action
			}
			data = JSON.stringify(data);
			
			$.post("backend/process_cable-editable.php", {data:data}, function(response){
				var responseJSON = JSON.parse(response);
				if (responseJSON.active == 'inactive'){
					window.location.replace("/");
				} else if ($(responseJSON.error).size() > 0){
					displayError(responseJSON.error);
				} else {
					
					if(action == 'lock') {
						$(linkEditable).html('<i class="fa fa-lock"></i>');
						$(linkEditable).attr('data-action', 'unlock');
						$(linkEditable).attr('title', 'Unlock cable for editing');
						
					} else if(action == 'unlock') {
						$(linkEditable).html('<i class="fa fa-unlock"></i>');
						$(linkEditable).attr('data-action', 'lock');
						$(linkEditable).attr('title', 'Lock cable for editing');
					}
				}
			});
		}
	});
	
	// Delete a temlate
	$('#modalConfirmBtn').click(function(){
		var selectedCableID = $(document).data('selectedCableID');
		
		var data = {
			cableID: selectedCableID,
			action: 'delete'
		}
		
		data = JSON.stringify(data);
		
		$.post("backend/process_cable-editable.php", {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				
				$('#'+selectedCableID).remove();
			}
		});
	});
});