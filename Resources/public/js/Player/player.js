$( document ).ready(function() {
	/* Gestion de la visualisation de la progression */	
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


	$('img').tooltip({placement:'top'});

	/* AJAX - Gestion des ressources héritées */
	var url = $("#step-id").val();
	$.ajax({
	    type: "GET",
	    url: url,
        success: function(heritedResources){
        	$("#herited-resources").html(heritedResources);
        	$('img').tooltip({placement:'top'});
        }
	});

});
