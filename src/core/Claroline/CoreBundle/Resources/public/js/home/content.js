(function () {
    "use strict";

    var homePath = $("#homePath").html(); //global

    if (!homePath) {
        homePath = "./";
    }

    /**
     * Get the parent node of an element by her ClassName.
     *
     * @param [DOM obj] element The element child.
     * @param [String] classname The class name of the parent node.
     *
     * @return [DOM obj]
     */
    function parentByClassName(element, classname)
    {
        if (element.parentNode !== undefined && element.parentNode !== null) {
            if (element.parentNode.className.indexOf(classname) !== -1) {
                return element.parentNode;
            } else {
                return parentByClassName(element.parentNode, classname);
            }
        }

        return null;
    }

    function modal(url, id, element)
    {
        $(".modal").modal("hide");

        id = typeof(id) !== "undefined" ? id : null;
        element = typeof(element) !== "undefined" ? element : null;

        $.ajax(homePath + url)
            .done(
                function (data)
                {
                    var modal = document.createElement("div");
                    modal.className = "modal fade";

                    if (id) {
                        modal.setAttribute("id", id);
                    }

                    if (element) {
                        $(modal).data("element", element);
                    }

                    modal.innerHTML = data;

                    $(modal).appendTo("body");

                    $(modal).modal("show");

                    $(modal).on("hidden.bs.modal", function () {
                        $(this).remove();
                    });

                }
        )
            .error(
                    function ()
                    {
                        alert("An error occurred!\n\nPlease try again later or check your internet connection");
                    }
                  )
            ;

    }

    /**
     * This function resize the height of a textarea relative of their content.
     *
     * @param [Textarea Obj] Obj The textarea to resize.
     */
    function resize(obj)
    {
        var lineheight = $(obj).css("line-height").substr(0, $(obj).css("line-height").indexOf("px"));
        var lines = $(obj).val().split("\n").length;

        lineheight = parseInt(lineheight, 10) + 4;

        $(obj).css("height", ((lines + 1) * lineheight) + "px");
    }


     /**
     * Verify if a string is a valid url.
     *
     * @param [String] url The url to verify.
     *
     * @TODO allow http://fr.wikipedia.org/wiki/Wikip%C3%A9dia:Accueil_principal
     *
     * @return this function returns true in success, otherwise false
     */
    function isurl(url) {
        var pattern = new RegExp(
            "^(https?:\\/\\/)?" + // protocol
            "((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|" + // domain name
            "((\\d{1,3}\\.){3}\\d{1,3}))" + // OR ip (v4) address
            "(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*" + // port and path
            "(\\?[;&a-z\\d%_.~+=-]*)?" + // query string
            "(\\#[-a-z\\d_]*)?$", "i" // fragment locator
            );

        if (pattern.test(url)) {
            return true;
        }

        return false;
    }

    /**
     * Create and update an element by POST method with ajax.
     *
     * @param [DOM obj] element The .creator element
     * @param [String] id The id of the content, this parameter is optional.
     *
     * @TODO Prevent multiple clicks
     */
    function creator(element, id)
    {
        id = typeof(id) !== "undefined" ? id : null;

        var creatorElement = parentByClassName(element, "creator");
        var title = creatorElement.getElementsByTagName("input")[0];
        var text = creatorElement.getElementsByTagName("textarea")[0];
        var type = $(creatorElement).data("type");
        var father = $(creatorElement).data("father");
        var generatedContent = "";
        var path = "";
        var contentPath = "";

        if (id) {
            path = "content/update/" + id;
        } else {
            path = "content/create";
        }

        if ($(creatorElement).find(".generated-content").html()) {
            generatedContent = $(creatorElement).find(".generated-content").html();
        }

        if (text.value !== "" || title.value !== "") {
            $.post(homePath + path,
                {
                    "title": title.value,
                    "text": text.value,
                    "generated": generatedContent,
                    "type": type,
                    "father": father
                }
            )
                .done(
                    function (data)
                    {
                        if (!isNaN(data) && data !== "") {
                            contentPath = "content/" + data + "/" + type;

                            var insertElement = function (content) {
                                $(creatorElement).next().prepend(content).hide().fadeIn("slow");
                            };

                            if (father) {
                                contentPath = "content/" + data + "/" + type + "/" + father;
                                creatorElement = parentByClassName(creatorElement, "creator" + father);

                                insertElement = function (content)
                                {
                                    $(creatorElement).after(content);
                                    $(creatorElement).find(".collapse").collapse("hide");
                                };

                            }

                            $.ajax(homePath + contentPath)
                                .done(
                                        function (data)
                                        {
                                            insertElement(data);
                                        }
                                )
                            ;

                            title.value = "";
                            text.value = "";
                            resize(text);
                            $(creatorElement).find(".generated").html("");

                        } else if (data === "true") {

                            contentPath = "content/" + id + "/" + type;

                            if (father) {
                                creatorElement = parentByClassName(creatorElement, "creator" + father);
                                contentPath = "content/" + id + "/" + type + "/" + father;
                            }

                            $.ajax(homePath + contentPath)
                                 .done(
                                    function (data)
                                    {
                                        $(creatorElement).replaceWith(data);
                                    }
                                )
                            ;

                        } else {
                            modal("content/error");
                        }
                    }
                )
                .error(
                    function ()
                    {
                        modal("content/error");
                    }
                  )
            ;

        }
    }

    /**
     * Get content from a external url and put it in a creator of contents.
     *
     * @param [HTML obj] textarea The textarea of the creator of contents.
     */
    function generatedContent(textarea)
    {
        $.post(homePath + "content/graph", { "generated_content_url": textarea.value })
            .done(
                function (data)
                {
                    if (data !== "false") {
                        $(textarea).parent().find(".generated").html(data);
                    }
                }
             )
            .error(
                function ()
                {
                    modal("content/error");
                }
            )
        ;
    }

    /** DOM events **/

    $("body").on("mouseenter", ".content-element", function () {
        $(".content-menu").first().addClass("hide"); // prevent some errors with the drop dawn
        $(this).find(".content-menu").first().removeClass("hide");

    });

    $("body").on("mouseleave", ".content-element", function () {
        if (!$(this).find(".content-menu").first().hasClass("open")) {
            $(this).find(".content-menu").first().addClass("hide");
        }
    });

    $("body").on("click", ".content-size", function (event) {
        var element = parentByClassName(event.target, "content-element");
        var size = (element.className.match(/\bcontent-\d+/g) || []).join(" ").substr(8);
        var id = $(element).data("id");
        var type = $(element).data("type");

        modal("content/size/" + id + "/" + size + "/" + type, "sizes", element);
    });

    $("body").on("click", "#sizes .panel", function (event) {
        var size = "content-" + event.target.innerHTML;
        var id = $("#sizes .modal-body").data("id");
        var type = $("#sizes .modal-body").data("type");
        var element = $("#sizes").data("element");

        if (id && type && element) {
            $.post(homePath + "content/update/" + id, { "size": size, "type": type })
        .done(
            function (data)
            {
                if (data === "true") {
                    $(element).removeClass(function (index, css) {
                        return (css.match(/\bcontent-\d+/g) || []).join(" ");
                    });

                    $(element).addClass(size);
                    $(element).trigger("DOMSubtreeModified"); //height resize event
                    $("#sizes").modal("hide");

                } else {
                    modal("content/error");
                }
            }
            )
        .error(
                function ()
                {
                    modal("content/error");
                }
              )
        ;
        }
    });

    $("body").on("click", ".content-region", function (event) {
        var element = parentByClassName(event.target, "content-element");
        var id = $(element).data("id");

        modal("content/region/" + id, "regions", element);
    });


    $("body").on("click", "#regions .panel", function (event) {
        var name = $(event.target).data("region");
        var id = $("#regions .modal-body").data("id");

        if (id && name) {
            $.ajax(homePath + "region/" + name + "/" + id)
                .done(
                    function ()
                    {
                        location.reload();
                    }
                )
                .error(
                    function ()
                    {
                        modal("content/error");
                    }
                )
            ;
        }
    });

    $("body").on("click", ".content-delete", function (event) {
        var element = parentByClassName(event.target, "content-element");

        modal("content/confirm", "delete-content", element);
    });

    $("body").on("click", "#delete-content .btn.delete", function () {
        var element = $("#delete-content").data("element");
        var id = $(element).data("id");

        if (id && element) {
            $.ajax(homePath + "content/delete/" + id)
            .done(
                function (data)
                {
                    if (data === "true") {
                        $(element).hide("slow", function () {
                            $(this).remove();
                        });
                    } else {
                        modal("content/error");
                    }
                }
            )
            .error(
                function ()
                {
                    modal("content/error");
                }
            );
        }
    });

    $("body").on("click", ".type-delete", function (event) {
        var element = parentByClassName(event.target, "alert");

        modal("content/confirm", "delete-type", element);
    });

    $("body").on("click", "#delete-type .btn.delete", function () {
        var element = $("#delete-type").data("element");
        var id = $(element).data("id");

        if (id && element) {
            $.ajax(homePath + "content/deletetype/" + id)
            .done(
                function (data)
                {
                    if (data === "true") {
                        $(element).parent().hide("slow", function () {
                            $(this).remove();
                        });
                    } else {
                        modal("content/error");
                    }
                }
            )
            .error(
                function ()
                {
                    modal("content/error");
                }
            );
        }
    });

    $("body").on("click", ".create-type", function (event) {
        var typeCreator = parentByClassName(event.target, "creator");
        var name = $("input", typeCreator);

        if (creator && name.val()) {
            $.ajax(homePath + "content/typeexist/" + name.val())
            .done(
                function (data)
                {
                    if (data === "false") {
                        $.ajax(homePath + "content/createtype/" + name.val())
                        .done(
                            function (data)
                            {
                                if (data !== "false" && data !== "") {
                                    $(typeCreator).next().prepend(data);
                                    name.val("");
                                } else {
                                    modal("content/error");
                                }
                            }
                        )
                        .error(
                            function ()
                            {
                                modal("content/error");
                            }
                        );
                    } else {
                        modal("content/typeerror");
                    }
                }
            );
        }
    });

    $("body").on("click", ".content-edit", function (event) {
        var element = parentByClassName(event.target, "content-element");
        var id = $(element).data("id");
        var type = $(element).data("type");
        var father = $(element).data("father");

        if (id && type && element) {
            var contentPath = "content/creator/" + type + "/" + id;

            if (father) {
                contentPath = "content/creator/" + type + "/" + id + "/" + father;
            }

            $.ajax(homePath + contentPath)
                .done(
                    function (data)
                    {
                        $(element).replaceWith(data);

                        $(".creator textarea").each(function () {
                            resize(this);
                        });
                    }
                )
                .error(
                    function ()
                    {
                        modal("content/error");
                    }
                )
            ;
        }
    });

    $("body").on("click", ".creator-button", function (event) {
        creator(event.target);
    });

    $("body").on("click", ".creator .edit-button", function (event) {
        var element = parentByClassName(event.target, "creator");
        var id = $(element).data("id");

        if (element && id) {
            creator(event.target, id);
        }
    });

    $("body").on("click", ".creator .cancel-button", function (event) {
        var element = parentByClassName(event.target, "creator");
        var id = $(element).data("id");
        var type = $(element).data("type");
        var father = $(element).data("father");

        if (id && type && element) {
            var contentPath = "content/" + id + "/" + type;

            if (father) {
                element = parentByClassName(element, "creator" + father);
                contentPath = "content/" + id + "/" + type + "/" + father;
            }

            $.ajax(homePath + contentPath)
                .done(
                    function (data)
                    {
                        $(element).replaceWith(data);
                    }
                )
                .error(
                    function ()
                    {
                        modal("content/error");
                    }
                )
            ;
        }
    });

    $(".creator textarea").each(function () {
        resize(this);
    });

    $("body").on("keyup", ".creator textarea", function (event) {

        if (event && event.keyCode) {
            if (event.keyCode === 13 || event.keyCode === 86 || event.keyCode === 8 || event.keyCode === 46) {
                resize(this);
            }

            if (isurl(this.value) && event.keyCode === 86) {
                generatedContent(this);
            }
        }
    });

    $("body").on("click", ".generated .close", function (event) {
        $(event.target).parent().html("");
    });

    $(".row.contents").sortable({
        items: "> .content-element",
        cancel: "input,textarea,button,select,option,a.btn.dropdown-toggle,.dropdown-menu",
        cursor: "move"
    });

    $(".row.contents").on("sortupdate", function (event, ui) {
        if (this === ui.item.parent()[0]) {
            var a = $(ui.item).data("id");
            var b = $(ui.item).next().data("id");
            var type = $(ui.item).data("type");

            if (a && type) {
                $.ajax(homePath + "content/reorder/" + type + "/" + a + "/" + b)
                .error(
                        function ()
                        {
                            modal("content/error");
                        }
                    )
                ;
            }
        }
    });

}());

