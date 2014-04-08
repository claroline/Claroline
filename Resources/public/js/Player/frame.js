$( document ).ready(function() {

    /* gestion du click sur les boutons affichant les ressources/step.text */
    $('.path-tab-btn').on('click', function () {
        var pathTab = $($(this).attr("data-target"));
        var iframe = $($(this).attr("data-target") + " iframe");

        toggleActiveClass($(this));
        
        $(".path-tab").hide();
        pathTab.show();

        // on ne charge l'iframe qu'au premier clic. 
        if (iframe.attr("src") == "") {
            iframe.attr("src", iframe.attr("data-resource-src"));
        } else {
            resizeIframe(iframe);
        }
    });

    $('.resource-frame').on('load', function () {
        resizeIframe($(this));
    });  
});


function resizeIframe(frame){
    frame.animate({ height: frame.contents().find("#wrap").height() + 20}, 200, function() {});
}

function toggleActiveClass(btn){
    $(".path-tab-btn").removeClass("active-resource");
    if (btn.hasClass('resource-tab-btn')){
        btn.addClass("active-resource");
    }
}