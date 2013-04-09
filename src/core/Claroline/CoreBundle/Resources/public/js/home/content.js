var asset = $("#asset").html(); //global

if (asset) {
    asset = asset + "app_dev.php/";
}
else {
    asset = "?/";
}

/**
 * Get the parent node of an element by her ClassName.
 *
 * @param [DOM obj] element The element child.
 * @param [String] classname The class name of the parent node.
 * 
 * @return [DOM obj]
 */
function parentByClassName(element, classname) {
    if (element.parentNode != undefined && element.parentNode != null) {
        if (element.parentNode.className.indexOf(classname) != -1) {
            return element.parentNode;
        }
        else {
            return parentByClassName(element.parentNode, classname);
        }
    }

    return null;
}

/**
 * Create an element by POST method with ajax.
 * 
 * @param [DOM obj] element The .creator element
 * @TODO Prevent multiple clicks
 */ 
function creator(element)
{
    var creator = parentByClassName(element, "creator");
    var title = creator.getElementsByTagName("input")[0];
    var text = creator.getElementsByTagName("textarea")[0];
    var type = $(creator).data("type");

    if (text.value != "" || title.value != "") {
        $.post( asset + "content/create", { "title": title.value, "text": text.value, "type": type })
            .done(
                    function(data)
                    {
                        if (!isNaN(data)) {
                            $.ajax( asset + "content/" + data + "/" + type )
                                 .done(
                                    function(data)
                                    {
                                        $(creator).next().prepend(data);
                                    }
                                )
                            ;
                            
                            title.value = "";
                            text.value = "";
                        }
                        else {
                            modal("content/error");
                        }
                    }
                    )
            .error(
                    function(data)
                    {
                            modal("content/error");
                    }
                  )
            ;

    }
}

function modal(url, id, element)
{
	id = typeof(id) != 'undefined' ? id : null;
    element = typeof(element) != 'undefined' ? element : null;

    $.ajax( asset + url )
        .done(
                function(data)
                {

                    var modal = document.createElement("div");
		            modal.className = "modal hide fade";

                    if (id) {
                        modal.setAttribute("id", id);
                    }

                    if (element) {
                        $(modal).data("element", element);
                    }

                    modal.innerHTML = data;

                    $(modal).appendTo("body");

                    $(modal).modal("show");

                    $(modal).on('hidden', function () {
                        $(this).remove();    
                    });
                    
                }
              )
        .error(
                function(data)
                {
                    modal("content/error");
                }
              )
        ;

}

/** DOM events **/

$("body").on("mouseenter", ".content-element", function() {
    $(".content-menu").addClass("hide"); // prevent some errors with the drop dawn
    $(this).find(".content-menu").removeClass("hide");

});

$("body").on("mouseleave", ".content-element", function() {
    if (!$(this).find(".content-menu").hasClass("open")) {
        $(this).find(".content-menu").addClass("hide");
    }
});

$("body").on("click", ".creator-button", function(event) {
    creator(event.target);
});

$("body").on("click", ".content-size", function(event){
    var element = parentByClassName(event.target, 'content-element');
    var size = (element.className.match (/\bspan\S+/g) || []).join(' ').substr(4);
    var id = $(element).data("id");
    var type = $(element).data("type");

    modal("content/size/" + id + "/" + size + "/" + type, "sizes", element);
});

$("body").on("click", ".content-delete", function(event){
    var element = parentByClassName(event.target, 'content-element');
    
    modal("content/confirm", "delete-content", element);
});

$("body").on("click", "#delete-content a.delete", function(){
    var element = $("#delete-content").data("element");
    var id = $(element).data("id");
    
    if (id && element) {        
        $.ajax( asset + "content/delete/" + id )
        .done(
                function(data)
                {
                    if (data == "true") {
                        $(element).hide('slow', function(){ $(this).remove(); });
                    }
                    else
                    {
                        modal("content/error");
                    }
                }
                )
        .error(
                function(data)
                {
                    modal("content/error");
                }
              )
        ;
    }
});

$("body").on("click", "#sizes a.border", function(event){
    var size = "span" + event.target.innerHTML;
    var id = $("#sizes .modal-body").data("id");
    var type = $("#sizes .modal-body").data("type");
    var element = $("#sizes").data("element");

    if (id && type && element) {  
        $.post( asset + "content/update/" + id, { "size": size, "type": type })
        .done(
                function(data)
                {
                    if (data == "true") {
                        $(element).removeClass (function (index, css) {
                            return (css.match (/\bspan\S+/g) || []).join(' ');
                        });

                        $(element).addClass(size);

                        $("#sizes").modal("hide");
                    }
                    else
                    {
                        modal("content/error");
                    }
                }
                )
        .error(
                function(data)
                {
                    modal("content/error");
                }
              )
        ;
    }
});

//@TODO find a way to atach of new elements
$(".creator textarea").css("height", function(){
    return (2 * $(this).css("line-height").substr(0, $(this).css("line-height").indexOf("px"))) + "px"
});

//@TODO find a way to atach of new elements
$(".creator textarea").keyup(function(event){
    
    if (event && event.keyCode){
        if (event.keyCode == 13 | event.keyCode == 86 | event.keyCode == 8 | event.keyCode == 46) {
            var lineheight = $(this).css("line-height").substr(0, $(this).css("line-height").indexOf("px"));
            var lines = $(this).val().split("\n").length;

            $(this).css("height", ((lines + 1)*lineheight) + "px");
        }
    }
});

