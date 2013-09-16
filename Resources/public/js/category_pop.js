function linkCategory(categoryNewPop) {
    //"use strict";
    //deplacer bouton créer une nouvelle catégorie
    $("*[id$='_interaction_question']").contents("div:nth-child(2)").append($("#lien_category"));

    $("#lien_category").click(function () {
        $.ajax({
            type: "POST",
            url: categoryNewPop,
            cache: false,
            success: function (data) {
                categoryPop(data);
            }
        });
    });
}

function categoryPop(data) {
    //"use strict";

    $("#overlayEffect_cat").css({
        "display": "none",
        "position": "fixed",
        "opacity": "0.7",
        "height": "100%",
        "width": "100%",
        "top": "0",
        "left": "0",
        "background": "-moz-linear-gradient(rgba(11,11,11,0.1), rgba(11,11,11,0.6)) repeat-x rgba(11,11,11,0.2)",
        "background": "-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(11,11,11,0.1)), to(rgba(11,11,11,0.6))) repeat-x rgba(11,11,11,0.2)",
        "z-index": "1"
    });


    $("#popupCategory").css({
        "position": "fixed",
        "left": "30%",
        "top": "40%",
        "width": "600px",
        "background":"url('images/body_bg.jpg') repeat-x left bottom #e5e5e5",
        "border": "5px solid #cecece",
        "z-index": "2",
        "padding": "10px",
        "border": "1px solid rgba(33, 33, 33, 0.6)",
        "-moz-box-shadow": "0 0 2px rgba(255, 255, 255, 0.6) inset",
        "-webkit-box-shadow": "0 0 2px rgba(255, 255, 255, 0.6) inset",
        "box-shadow": "0 0 2px rgba(255, 255, 255, 0.6) inset"
    });

    $("#closeCategory").css({
        //"background":"red",
        "cursor": "pointer",
        //"width":"25px",
        "width": "50px",
        "height": "26px",
        "position": "fixed",
        "z-index": "3200",
        "position": "absolute",
        "top": "-25px",
        "right": "-22px"
    });


    $("#div_input_category").html(data);
    var popup = false;
    if (popup === false) {
        $("#overlayEffect_cat").fadeIn("slow");
        $("#popupCategory").fadeIn("slow");
        $("#closeCategory").fadeIn("slow");
        popup = true;
    }

    //////////

    $("#closeCategory").click(function () {
        //acutaliser la liste de category

        hidePopup();
    });

    $("#overlayEffect_cat").click(function () {
        hidePopup();
    });

    function hidePopup() {
        //"use strict";
        if (popup === true) {
            $("#overlayEffect_cat").fadeOut("slow");
            $("#popupCategory").fadeOut("slow");
            $("#closeCategory").fadeOut("slow");
            popup = false;
        }
    }
}