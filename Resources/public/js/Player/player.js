$( document ).ready(function() {
	$("*").tooltip({placement:'top'});

	$( ".popup" ).click(function() {
		$("#popup-iframe").attr("src",$(this).attr("data-target") + "?_mode=path");
		$("#popup").show();
	});

	$( "#popup-close" ).click(function() {
		$("#popup").hide();
		$("#popup-iframe").attr("src", "");
	});
});




/*
function launchFullScreen(element) {
	if (element.requestFullscreen)
		{ element.requestFullscreen(); }
	else if (element.mozRequestFullScreen)
		{ element.mozRequestFullScreen(); }
	else if (element.webkitRequestFullscreen)
		{ element.webkitRequestFullscreen(); }
	else if (element.msRequestFullscreen)
		{ element.msRequestFullscreen(); }
}
*/