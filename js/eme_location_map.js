// console.log("eventful: " + eventful + " scope " + scope);

jQuery(document.body).unload(function() {
	GUnload();
});

jQuery(document).ready(function() {
	loadMapScript();
});

function htmlDecode(value){ 
  return jQuery('<div/>').html(value).text(); 
}

function loadGMap() {
	// first the global map (if present)
	if (document.getElementById("eme_global_map")) {
		var locations;
		jQuery.getJSON(document.URL,{ajax: 'true', query:'GlobalMapData', eventful:eventful, scope:scope, category:category}, function(data) {
			locations = data.locations;
			var latitudes = new Array();
			var longitudes = new Array();
			var max_latitude = -500.1;
			var min_latitude = 500.1;
			var max_longitude = -500.1;
			var min_longitude = 500.1;

			var zoom_factor=parseInt(data.zoom_factor);
			var maptype=data.maptype;
			var enable_zooming=false;
			if (data.enable_zooming === 'true') {
				enable_zooming = true;
			}

			var mapCenter = new google.maps.LatLng(45.4213477,10.952397);
                        
			var myOptions = {
				zoom: zoom_factor,
				center: mapCenter,
				disableDoubleClickZoom: true,
				scrollwheel: enable_zooming,
				mapTypeControlOptions: {
					mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE, google.maps.MapTypeId.HYBRID, google.maps.MapTypeId.TERRAIN]
				},
				mapTypeId: google.maps.MapTypeId[maptype]
			};
			var map = new google.maps.Map(document.getElementById("eme_global_map"), myOptions);
			var infowindow = new google.maps.InfoWindow();

			jQuery.each(locations, function(i, item) {
				latitudes.push(item.location_latitude);
				longitudes.push(item.location_longitude);
				if (parseFloat(item.location_latitude) > max_latitude) {
					max_latitude = parseFloat(item.location_latitude);
				}
				if (parseFloat(item.location_latitude) < min_latitude) {
					min_latitude = parseFloat(item.location_latitude);
				}
				if (parseFloat(item.location_longitude) > max_longitude) {
					max_longitude = parseFloat(item.location_longitude);
				}
				if (parseFloat(item.location_longitude) < min_longitude) {
					min_longitude = parseFloat(item.location_longitude); 
				}
			});

			//console.log("Latitudes: " + latitudes + " MAX: " + max_latitude + " MIN: " + min_latitude);
			//console.log("Longitudes: " + longitudes +  " MAX: " + max_longitude + " MIN: " + min_longitude);

			center_lat = min_latitude + (max_latitude - min_latitude)/2;
			center_lon = min_longitude + (max_longitude - min_longitude)/2;
			//console.log("center: " + center_lat + " - " + center_lon) + min_longitude;

			lat_interval = max_latitude - min_latitude;

			//vertical compensation to fit in the markers
			vertical_compensation = lat_interval * 0.1;

			var locationsBound = new google.maps.LatLngBounds(new google.maps.LatLng(max_latitude + vertical_compensation,min_longitude),new google.maps.LatLng(min_latitude,max_longitude) );
			//console.log(locationsBound);
			map.fitBounds(locationsBound);
			map.setCenter(new google.maps.LatLng(center_lat + vertical_compensation,center_lon)); 

			jQuery.each(locations, function(index, item) {
				var letter;
				if (index>25) {
					var rest=index%26;
					var firstindex=Math.floor(index/26)-1;
					letter = String.fromCharCode("A".charCodeAt(0) + firstindex)+String.fromCharCode("A".charCodeAt(0) + rest);
				} else {
					letter = String.fromCharCode("A".charCodeAt(0) + index);
				}

				customIcon = location.protocol + "//chart.apis.google.com/chart?chst=d_map_pin_letter&chld="+letter+"|FF0000|000000";
				//shadow = "http://chart.apis.google.com/chart?chst=d_map_pin_shadow";
				var point = new google.maps.LatLng(parseFloat(item.location_latitude), parseFloat(item.location_longitude));
				var balloon_id = "eme-location-balloon-id";
				var balloon_content = "<div id=\""+balloon_id+"\" class=\"eme-location-balloon\">"+htmlDecode(item.location_balloon)+"</div>";
				infowindow.balloon_id = balloon_id;
				var marker = new google.maps.Marker({
					position: point,
					map: map,
					icon: customIcon,
					infowindow: infowindow,
					infowindowcontent: balloon_content
				});
				if (document.getElementById('location-'+item.location_id)) {
				   jQuery('li#location-'+item.location_id+' a').click(function() {
				   	infowindow.setContent(balloon_content);
				   	infowindow.open(map,marker);
				   	jQuery(window).scrollTop(jQuery('#eme_global_map').position().top);
				   });
				}
				google.maps.event.addListener(marker, "click", function() {
					// This also works, but relies on global variables:
					// infowindow.setContent(balloon_content);
					// infowindow.open(map,marker);
					// the content of marker is available via "this"
					this.infowindow.setContent(this.infowindowcontent);
					this.infowindow.open(this.map,this);
				});
			});
			// to remove the scrollbars: we unset the overflow
			// of the parent div of the infowindow
			google.maps.event.addListener(infowindow, 'domready', function() {
					document.getElementById(this.balloon_id).parentNode.style.overflow='';
					document.getElementById(this.balloon_id).parentNode.parentNode.style.overflow='';
			});

			// fitbounds plays with the zoomlevel, and zooms in too much if only 1 marker, or zooms out too much if no markers
			// solution taken from http://stackoverflow.com/questions/2989858/google-maps-v3-enforcing-min-zoom-level-when-using-fitbounds
			// Content:
			// At this discussion (http://groups.google.com/group/google-maps-js-api-v3/browse_thread/thread/48a49b1481aeb64c?pli=1)
			//   I discovered that basically when you do a fitBounds, the zoom happens "asynchronously" so you need to capture the
			//   zoom and bounds change event. The code in the final post worked for me with a small modification... as it stands it
			//   stops you zooming greater than 15 completely, so used the idea from the fourth post to have a flag set to only do
			//   it the first time.
			map.initialZoom = true;
			google.maps.event.addListener(map, 'zoom_changed', function() {
				zoomChangeBoundsListener = google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
					if (this.getZoom() > 14 && this.initialZoom === true) {
					// Change max/min zoom here
						this.setZoom(14);
						this.initialZoom = false;
					}
					if (this.getZoom() < 1 && this.initialZoom === true) {
						// Change max/min zoom here
						this.setZoom(1);
						this.initialZoom = false;
					}
               // we use addListenerOnce, so we don't need to remove the listener anymore
			      //	google.maps.event.removeListener(zoomChangeBoundsListener);
				});
			});
		});
	}

	// and now for the normal maps (if any)
	var divs = document.getElementsByTagName('div');
	for (var i = 0; i < divs.length; i++) {
		var divname = divs[i].id; 
		if(divname.indexOf("eme-location-map_") === 0) { 
			var map_id = divname.replace("eme-location-map_","");
			var lat_id = window['latitude_'+map_id]; 
			var lon_id = window['longitude_'+map_id]; 
			var map_text_id = window['map_text_'+map_id]; 
			var point = new google.maps.LatLng(lat_id, lon_id);

         var zoom_factor=window['zoom_factor_'+map_id];
         var maptype=window['maptype_'+map_id];
         var enable_zooming=false;
         if (window['enable_zooming_'+map_id] === 'true') {
            enable_zooming = true;
         }

			var mapCenter= new google.maps.LatLng(point.lat()+0.005, point.lng()-0.003);
			var myOptions = {
                           zoom: zoom_factor,
                           center: mapCenter,
                           disableDoubleClickZoom: true,
                           scrollwheel: enable_zooming,
                           mapTypeControlOptions: {
                                 mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE, google.maps.MapTypeId.HYBRID, google.maps.MapTypeId.TERRAIN]
                           },
                           mapTypeId: google.maps.MapTypeId[maptype]
			};
			var s_map = new google.maps.Map(divs[i], myOptions);
			var s_balloon_id= "eme-location-balloon-"+map_id;
			var s_infowindow = new google.maps.InfoWindow({
				content: "<div id=\"" + s_balloon_id +"\" class=\"eme-location-balloon\">"+map_text_id+"</div>",
				balloon_id: s_balloon_id
			});
			// we add the infowinfow object to the marker object, then we can call it in the 
			// google.maps.event.addListener and it always has the correct content
			// we do this because we have multiple maps as well ...
			var s_marker = new google.maps.Marker({
				position: point,
				map: s_map,
				infowindow: s_infowindow
			});
			s_infowindow.open(s_map,s_marker);
			google.maps.event.addListener(s_marker, "click", function() {
				// the content of s_marker is available via "this"
				this.infowindow.open(this.map,this);
			});
			// to remove the scrollbars: we unset the overflow
			// of the parent div of the infowindow
			google.maps.event.addListener(s_infowindow, 'domready', function() {
				document.getElementById(this.balloon_id).parentNode.style.overflow='';
				document.getElementById(this.balloon_id).parentNode.parentNode.style.overflow='';
			});
      }
	}
}

function loadMapScript() {
	var script = document.createElement("script");
//	script.setAttribute("src", "http://maps.google.com/maps?file=api&v=2.x&key=" + key + "&c&async=2&callback=loadGMap");
//	script.setAttribute("type", "text/javascript");
//	document.documentElement.firstChild.appendChild(script);
	script.type = "text/javascript";
	script.src = location.protocol + "//maps.google.com/maps/api/js?v=3.1&sensor=false&callback=loadGMap";
	document.body.appendChild(script);
}
