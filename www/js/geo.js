//var x = document.getElementById("geoStatus");

function getLocation() {
	if (navigator.geolocation) {
	  navigator.geolocation.getCurrentPosition(sendToServer);
	} else {
	  alert( "Geolocation is not supported by this browser.");
	}
}

function showPosition(position) {
	x.innerHTML = "Latitude: " + position.coords.latitude +
	"<br>Longitude: " + position.coords.longitude;
}

function sendToServer(position)
{
	var lat = position.coords.latitude;
	var long = position.coords.longitude;

	var data = {lat:lat, long:long};
	data = JSON.stringify(data);

	jQuery.ajax({
	  url:'/api/gameParams',
	  method:'POST',
	  type: 'application/JSON',
	  data: data,
	  success: function(response){
		console.log(response);
		window.gameParams=response;
	  },
	  error: function(response) {
		alert("Error loading data");
	  }
	});
}

getLocation();
// Test
//sendToServer({"coords":{"latitude":50.55232, "longditude":55.12345}});
