$( document ).ready(function() {

	$("*").tooltip({placement:'top'});


	$('.resource-frame').on('load', function () {
        var frame = $(this);
        frame.css("height", frame.contents().find("#wrap").height() + 20);
    });

   $('.resource-tab a').on('click', function () {
   		var iframe = $($(this).attr("href") +" iframe");
   		if (iframe.attr("src") == ""){
   			iframe.attr("src", iframe.attr("data-resource-src"));
   		}
   	});
});
