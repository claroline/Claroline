$( document ).ready(function() {
	$("*").tooltip({placement:'top'});

	$('.resource-frame').on('load', function () {
        var frame = $(this);
        //$("#loading-resource-" + frame.data("resource-id")).fadeOut(300);
        frame.animate({ height: frame.contents().find("#wrap").height() + 20}, 300, function() {});
    });

   $('.resource-tab a').on('click', function () {
   		var iframe = $($(this).attr("href") +" iframe");
   		if (iframe.attr("src") == ""){
   			iframe.attr("src", iframe.attr("data-resource-src"));
   		}
   	});

   $('.reload-resource').on('click', function () {
      var iframe = $("#frame-resource-"+$(this).data('resource-id'));
      iframe.attr("src", iframe.data("resource-src"));
    });

   $('#current-step-text a').attr('target','_blank');
});
