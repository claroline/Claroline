$( document ).ready(function() {
	$("*").tooltip({placement:'top'});

	$('.resource-frame').on('load', function () {
        var frame = $(this);
        frame.animate({ height: frame.contents().find("#wrap").height() + 20}, 300, function() {});
    });

   $('.resource-tab a').on('click', function () {
   		var iframe = $($(this).attr("href") +" iframe");
   		if (iframe.attr("src") == ""){
   			iframe.attr("src", iframe.attr("data-resource-src"));
   		}
   	});
});
