$( document ).ready(function() {	
	$("#step-map-body").hide();
			 
	$( "#step-map-open").click(function() {
		if($("#step-map-body").attr("data-status") == "closed"){
			$("#step-map-body").attr("data-status","opened");
			$("#step-map").addClass("well");
			$("#step-map-body").show();

			$("#step-map-open a").removeClass("icon-chevron-down");
			$("#step-map-open a").addClass("icon-chevron-up");
		}
		else{
			$("#step-map-body").attr("data-status","closed");
			$("#step-map-body").hide();
			$("#step-map").removeClass("well");
			$("#step-map-open a").removeClass("icon-chevron-up");
			$("#step-map-open a").addClass("icon-chevron-down");
		}
	});
});