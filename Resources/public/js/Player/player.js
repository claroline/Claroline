$( document ).ready(function() {

	$("*").tooltip({placement:'top'});

	/* IFRAME MANAGEMENT */
	$(".resource-frame").height($(window).height());

	$('.resource-frame').on('load', function () {
		var iframe = $(this);
		cleanFrame(iframe);
		iframe.show();
    });


   $('.resource-tab a').on('click', function () {
   		var iframe = $($(this).attr("href") +" iframe");
   		if (iframe.attr("src") == ""){
   			iframe.hide();
   			iframe.attr("src", iframe.attr("data-resource-src"));
   		}
   	});
});

function cleanFrame(iframe){
	iframe.contents().find("#top_bar").remove();
	iframe.contents().find("#left-bar").remove();
	iframe.contents().find("#footer").remove();
	iframe.contents().find("#push").remove();
	iframe.contents().find("body").css("padding", "10px").removeClass("left-bar-push");
	iframe.contents().find("body").css("overflow-x", "hidden");
	iframe.contents().find("#wrap>.container").removeClass("container");
}

/*
function launchFullScreen(element) {
	if (element.requestFullscreen){ 
		element.requestFullscreen(); 
	}
	else if (element.mozRequestFullScreen){ 
		element.mozRequestFullScreen(); 
	}
	else if (element.webkitRequestFullscreen){ 
		element.webkitRequestFullscreen(); 
	}
	else if (element.msRequestFullscreen){ 
		element.msRequestFullscreen(); 
	}
}
*/