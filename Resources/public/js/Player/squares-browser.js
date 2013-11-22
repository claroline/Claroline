$( document ).ready(function() {
	$('.step').tooltip({placement:'top'});
	
	$( ".toggle-sibling" ).click(function() {
		var lvl = $( this ).attr("data-lvl");
		if($(this).hasClass("hide-sibling")){
			$(".shown.lvl-"+lvl).removeClass("shown").addClass("hidden");
			$( this ).removeClass("hide-sibling icon-minus-sign").addClass("show-sibling icon-plus-sign");
		}
		else{
			$(".hidden.lvl-"+lvl).removeClass("hidden").addClass("shown");
			$(this).removeClass("show-sibling icon-plus-sign").addClass("hide-sibling icon-minus-sign");
		}
	});
});
