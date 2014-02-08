$j_eme=jQuery.noConflict();

function isoStringToDate(s) {
  var b = s.split(/[-t:+]/ig);
    // FB sends date in two formats, sometimes it avoids time
  if(b.length === 3) {
     return new Date(b[0], --b[1], b[2]);
  } else {
     return new Date(b[0], --b[1], b[2], b[3], b[4], b[5]);
  }
  //return new Date(Date.UTC(b[0], --b[1], b[2], b[3], b[4], b[5]));
}

function remove_booking() {
	eventId = ($j_eme(this).parents('table:first').attr('id').split("-"))[3]; 
	idToRemove = ($j_eme(this).parents('tr:first').attr('id').split("-"))[1];
	$j_eme.ajax({
  	  type: "POST",
	    url: "admin.php?page=eme-people&action=remove_booking",
	    data: "booking_id="+ idToRemove,
	    success: function(){
				$j_eme('tr#booking-' + idToRemove).fadeOut('slow');
				update_booking_data();
	   		}
	});
}
 
function update_booking_data () {
  	$j_eme.getJSON("admin.php?page=eme-people&eme_ajax_action=booking_data",{event_id: eventId, ajax: 'true'}, function(data){
  	  	booked = data[0].bookedSeats;
		available = data[0].availableSeats; 
		$j_eme('td#booked-seats').text(booked);
		$j_eme('td#available-seats').text(available);
 	});
}

function areyousuretodeny() {
   if ($j_eme("select[name=action]").val() == "denyRegistration") {
      if (!confirm("Are you sure you want to deny registration for these bookings?")) {
         return false;
      } else {
         return true;
      }
   }
   return true;
}

$j_eme(document).ready( function() {
    // Managing bookings delete operations 
      $j_eme('a.bookingdelbutton').click(remove_booking);
      $j_eme('#eme-admin-pendingform').bind("submit", areyousuretodeny);
      $j_eme('#eme-admin-changeregform').bind("submit", areyousuretodeny);

      $j_eme('input.select-all').change(function() {
         if ($j_eme(this).is(':checked')) {
            $j_eme('input.row-selector').attr('checked', true);
         } else {
            $j_eme('input.row-selector').attr('checked', false);
         }
      });
});

jQuery(document).ready( function($) {
	jQuery('#mtm_add_tag').click( function(event) {
		event.preventDefault();
		//Get All meta rows
			var metas = jQuery('#mtm_body').children();
		//Copy first row and change values
			var metaCopy = jQuery(metas[0]).clone(true);
			newId = metas.length + 1;
			metaCopy.attr('id', 'mtm_'+newId);
			metaCopy.find('a').attr('rel', newId);
			metaCopy.find('[name=mtm_1_ref]').attr({
				name:'mtm_'+newId+'_ref' ,
				value:'' 
			});
			metaCopy.find('[name=mtm_1_content]').attr({ 
				name:'mtm_'+newId+'_content' , 
				value:'' 
			});
			metaCopy.find('[name=mtm_1_name]').attr({ 
				name:'mtm_'+newId+'_name' ,
				value:'' 
			});
		//Insert into end of file
			jQuery('#mtm_body').append(metaCopy);
		//Duplicate the last entry, remove values and rename id
	});
	
	jQuery('#import-fb-event-btn').click(function (e) {
		e.preventDefault();
		var url = jQuery('#fb-event-url').val();
		var eventID = url.split('facebook.com/events/')[1].split('/')[0];
		FB.api('/' + eventID,function (data) {
			jQuery('#title').val(data.name);
			tinyMCE.get('content').setContent(data.description.replace(/\n/ig,"<br>"));

			var startTime = isoStringToDate(data.start_time);
			var endTime = isoStringToDate(data.end_time);

			jQuery('#localised-start-date').datepick('setDate', startTime);
			jQuery('#localised-end-date').datepick('setDate', endTime);
			jQuery('#start-time').timeEntry('setTime', startTime);
			jQuery('#end-time').timeEntry('setTime', endTime);
			// not needed, jQuery('#location_address').val(data.location);
		});
	});

	jQuery('#mtm_body a').click( function(event) {
		event.preventDefault();
		//Only remove if there's more than 1 meta tag
		if(jQuery('#mtm_body').children().length > 1){
			//Remove the item
			jQuery(jQuery(this).parent().parent().get(0)).remove();
			//Renumber all the items
			jQuery('#mtm_body').children().each( function(i){
				metaCopy = jQuery(this);
				oldId = metaCopy.attr('id').replace('mtm_','');
				newId = i+1;
				metaCopy.attr('id', 'mtm_'+newId);
				metaCopy.find('a').attr('rel', newId);
				metaCopy.find('[name=mtm_'+ oldId +'_ref]').attr('name', 'mtm_'+newId+'_ref');
				metaCopy.find('[name=mtm_'+ oldId +'_content]').attr('name', 'mtm_'+newId+'_content');
				metaCopy.find('[name=mtm_'+ oldId +'_name]').attr( 'name', 'mtm_'+newId+'_name');
			});
		} else {
			metaCopy = jQuery(jQuery(this).parent().parent().get(0));
			metaCopy.find('[name=mtm_1_ref]').attr('value', '');
			metaCopy.find('[name=mtm_1_content]').attr('value', '');
			metaCopy.find('[name=mtm_1_name]').attr( 'value', '');
			alert("If you don't want any meta tags, just leave the text boxes blank and submit");
		}
	});
});

