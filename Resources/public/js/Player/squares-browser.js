$( document ).ready(function() {
	$('.step').tooltip({placement:'top'});

	$( ".show-sibling" ).click(function() {
		var lvl = $( this ).attr("data-lvl");
		$(".hidden.lvl-"+lvl).removeClass("hidden");
	});

	$( ".hide-sibling" ).click(function() {
		var lvl = $( this ).attr("data-lvl");
		$(".hidden.lvl-"+lvl).removeClass("hidden");
	});

});
