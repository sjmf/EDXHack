var x = document.getElementById("geodata");
function getLocation() {

	if (navigator.geolocation) {
		jQuery("#geodata").html('<h3 style="display:inline">Loading Geographic Data&nbsp;<img src="img/ajax-loader.gif" style="display:inline"/>	</h3>');
	  navigator.geolocation.getCurrentPosition(sendToServer);
	} else {
	  jQuery("#geodata").text( "Geolocation is not supported by this browser.");
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

	x.innerHTML="<h3>Game Loaded- click play!</h3>";
	jQuery("#playbtn").show();
	window.gameParams = {"air":199.41839,"noise":46100};
	return;

	jQuery.ajax({
	  url:'/api/gameParams',
	  method:'POST',
	  type: 'application/JSON',
	  data: data,
	  success: function(response){
		console.log(response);
		window.gameParams=JSON.parse(response);
		x.innerHTML="<h3>Game Loaded- click play!</h3>";
		jQuery("#playbtn").show();
	  },
	  error: function(response) {
		alert("Error loading data");
	  }
	});
}

getLocation();
// Test
//sendToServer({"coords":{"latitude":50.55232, "longditude":55.12345}});
