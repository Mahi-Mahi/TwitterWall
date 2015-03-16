/* function : calculate distance(kilometer) between 2 latlng 
		id(optionnal) is html element's id */
function distanceMarkers(map, start, end, selector){
	var directionsService = new google.maps.DirectionsService();
	var  directionsDisplay = new google.maps.DirectionsRenderer();
	
	var request = {
		origin: start, 
		destination:end,
		travelMode: google.maps.DirectionsTravelMode.WALKING,
		unitSystem: google.maps.DirectionsUnitSystem.METRIC
	};
	
	directionsDisplay.setMap(map);
	directionsService.route(request, function(result, status){
	
		if (status == google.maps.DirectionsStatus.OK) {
		
			distance = result.routes[0].legs[0].distance.value;
			distance/=1000;
			if(selector!=undefined){
				jQuery(selector).text(distance);
			}
		}
		else{
			console.log("distanceMarkers => directionsService status : "+status);
		}
	});
}