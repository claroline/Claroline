//Home Functions
(function () {
    "use strict";

    window.Claroline.Home = {};
    var home = window.Claroline.Home;

    home.path = $("#homePath").html(); //global

    if (!home.path) {
        home.path = "./";
    }

    home.modal = function (url, id, element)
    {
        $(".modal").modal("hide");

        id = typeof(id) !== "undefined" ? id : null;
        element = typeof(element) !== "undefined" ? element : null;

        $.ajax(home.path + url)
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

    };

    /**
     * This function resize the height of a textarea relative of their content.
     *
     * @param [Textarea Obj] Obj The textarea to resize.
     */
    home.resize = function (obj)
    {
        var lineheight = $(obj).css("line-height").substr(0, $(obj).css("line-height").indexOf("px"));
        var lines = $(obj).val().split("\n").length;

        lineheight = parseInt(lineheight, 10) + 4;

        $(obj).css("height", ((lines + 1) * lineheight) + "px");
    };


    home.findUrls = function (text)
    {
        var source = (text || "").toString();
        var urlArray = [];
        var matchArray;

        // Regular expression to find FTP, HTTP(S) and email URLs.
        var regexToken =
        /(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})/g;

        // Iterate through any URLs in the text.
        while ((matchArray = regexToken.exec(source)) !== null) {
            var token = matchArray[0];
            urlArray.push(token);
        }

        return urlArray;
    };

    /**
     * Create and update an element by POST method with ajax.
     *
     * @param [DOM obj] element The .creator element
     * @param [String] id The id of the content, this parameter is optional.
     *
     * @TODO Prevent multiple clicks
     */
    home.creator = function (element, id)
    {
        id = typeof(id) !== "undefined" ? id : null;

        var creatorElement = $(element).parents(".creator").get(0);
        var title = $(".content-title", creatorElement).get(0);
        var text = $(".content-text", creatorElement).get(0);
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
            $.post(home.path + path,
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

                                insertElement = function (content)
                                {
                                    $(".creator" + father).after(content);
                                    $(".creator" + father).find(".collapse" + father).collapse("hide");
                                };
                            }

                            $.ajax(home.path + contentPath)
                                .done(
                                        function (data)
                                        {
                                            insertElement(data);
                                        }
                                )
                            ;

                            title.value = "";
                            text.value = "";
                            home.resize(text);
                            $(creatorElement).find(".generated").html("");

                        } else if (data === "true") {

                            contentPath = "content/" + id + "/" + type;

                            if (father) {
                                creatorElement = $(creatorElement).parents(".creator" + father).get(0);
                                contentPath = "content/" + id + "/" + type + "/" + father;
                            }

                            $.ajax(home.path + contentPath)
                                 .done(
                                    function (data)
                                    {
                                        $(creatorElement).replaceWith(data);
                                    }
                                )
                            ;

                        } else {
                            home.modal("content/error");
                        }
                    }
                )
                .error(
                    function ()
                    {
                        home.modal("content/error");
                    }
                  )
            ;

        }
    };

    /**
     * Get content from a external url and put it in a creator of contents.
     *
     * @param url The url of a webpage.
     */
    home.generatedContent = function (creator, url)
    {
        $.post(home.path + "content/graph", { "generated_content_url": url })
            .done(
                function (data)
                {
                    if (data !== "false") {
                        $(creator).find(".generated").html(data);
                    }
                }
             )
            .error(
                function ()
                {
                    home.modal("content/error");
                }
            )
        ;
    };

}());


//DOM events
(function () {
    "use strict";

    var home = window.Claroline.Home;

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
        var element = $(event.target).parents(".content-element").get(0);
        var size = (element.className.match(/\bcontent-\d+/g) || []).join(" ").substr(8);
        var id = $(element).data("id");
        var type = $(element).data("type");

        home.modal("content/size/" + id + "/" + size + "/" + type, "sizes", element);
    });

    $("body").on("click", "#sizes .panel", function (event) {
        var size = "content-" + event.target.innerHTML;
        var id = $("#sizes .modal-body").data("id");
        var type = $("#sizes .modal-body").data("type");
        var element = $("#sizes").data("element");

        if (id && type && element) {
            $.post(home.path + "content/update/" + id, { "size": size, "type": type })
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
                    home.modal("content/error");
                }
            }
            )
        .error(
                function ()
                {
                    home.modal("content/error");
                }
              )
        ;
        }
    });

    $("body").on("click", ".content-region", function (event) {
        var element = $(event.target).parents(".content-element").get(0);
        var id = $(element).data("id");

        home.modal("content/region/" + id, "regions", element);
    });


    $("body").on("click", "#regions .panel", function (event) {
        var name = $(event.target).data("region");
        var id = $("#regions .modal-body").data("id");

        if (id && name) {
            $.ajax(home.path + "region/" + name + "/" + id)
                .done(
                    function ()
                    {
                        location.reload();
                    }
                )
                .error(
                    function ()
                    {
                        home.modal("content/error");
                    }
                )
            ;
        }
    });

    $("body").on("click", ".content-delete", function (event) {
        var element = $(event.target).parents(".content-element").get(0);

        home.modal("content/confirm", "delete-content", element);
    });

    $("body").on("click", "#delete-content .btn.delete", function () {
        var element = $("#delete-content").data("element");
        var id = $(element).data("id");

        if (id && element) {
            $.ajax(home.path + "content/delete/" + id)
            .done(
                function (data)
                {
                    if (data === "true") {
                        $(element).hide("slow", function () {
                            $(this).remove();
                        });
                    } else {
                        home.modal("content/error");
                    }
                }
            )
            .error(
                function ()
                {
                    home.modal("content/error");
                }
            );
        }
    });

    $("body").on("click", ".type-delete", function (event) {
        var element = $(event.target).parents(".alert").get(0);

        home.modal("content/confirm", "delete-type", element);
    });

    $("body").on("click", "#delete-type .btn.delete", function () {
        var element = $("#delete-type").data("element");
        var id = $(element).data("id");

        if (id && element) {
            $.ajax(home.path + "content/deletetype/" + id)
            .done(
                function (data)
                {
                    if (data === "true") {
                        $(element).parent().hide("slow", function () {
                            $(this).remove();
                        });
                    } else {
                        home.modal("content/error");
                    }
                }
            )
            .error(
                function ()
                {
                    home.modal("content/error");
                }
            );
        }
    });

    $("body").on("click", ".create-type", function (event) {
        var typeCreator = $(event.target).parents(".creator").get(0);
        var name = $("input", typeCreator);

        if (typeCreator && name.val()) {
            $.ajax(home.path + "content/typeexist/" + name.val())
            .done(
                function (data)
                {
                    if (data === "false") {
                        $.ajax(home.path + "content/createtype/" + name.val())
                        .done(
                            function (data)
                            {
                                if (data !== "false" && data !== "") {
                                    $(typeCreator).next().prepend(data);
                                    name.val("");
                                } else {
                                    home.modal("content/error");
                                }
                            }
                        )
                        .error(
                            function ()
                            {
                                home.modal("content/error");
                            }
                        );
                    } else {
                        home.modal("content/typeerror");
                    }
                }
            );
        }
    });

    $("body").on("click", ".content-edit", function (event) {
        var element = $(event.target).parents(".content-element").get(0);
        var id = $(element).data("id");
        var type = $(element).data("type");
        var father = $(element).data("father");

        if (id && type && element) {
            var contentPath = "content/creator/" + type + "/" + id;

            if (father) {
                contentPath = "content/creator/" + type + "/" + id + "/" + father;
            }

            $.ajax(home.path + contentPath)
                .done(
                    function (data)
                    {
                        $(element).replaceWith(data);

                        $(".creator textarea").each(function () {
                            home.resize(this);
                        });
                    }
                )
                .error(
                    function ()
                    {
                        home.modal("content/error");
                    }
                )
            ;
        }
    });

    $("body").on("click", ".creator-button", function (event) {
        home.creator(event.target);
    });

    $("body").on("click", ".creator .edit-button", function (event) {
        var element = $(event.target).parents(".creator").get(0);
        var id = $(element).data("id");

        if (element && id) {
            home.creator(event.target, id);
        }
    });

    $("body").on("click", ".creator .cancel-button", function (event) {
        var element = $(event.target).parents(".creator").get(0);
        var id = $(element).data("id");
        var type = $(element).data("type");
        var father = $(element).data("father");

        if (id && type && element) {
            var contentPath = "content/" + id + "/" + type;

            if (father) {
                element = $(element).parents(".creator" + father).get(0);
                contentPath = "content/" + id + "/" + type + "/" + father;
            }

            $.ajax(home.path + contentPath)
                .done(
                    function (data)
                    {
                        $(element).replaceWith(data);
                    }
                )
                .error(
                    function ()
                    {
                        home.modal("content/error");
                    }
                )
            ;
        }
    });

    $(".creator textarea").each(function () {
        home.resize(this);
    });

    $("body").on("keyup", ".creator textarea", function (event) {

        if (event && event.keyCode) {
            if (event.keyCode === 13 || event.keyCode === 86 || event.keyCode === 8 || event.keyCode === 46) {
                home.resize(this);
            }
        }
    });

    $("body").on("click", ".creator .addlink", function () {
        var element = $(event.target).parents(".creator").get(0);

        home.modal("content/link", "add-link", element);
    });

    $("body").on("click", "#add-link .btn-primary", function () {
        var urls = home.findUrls($("#add-link input").val());
        var modal = $(this).parents(".modal").get(0);
        var creator = $("#add-link").data("element");

        if (urls.length > 0) {
            home.generatedContent(creator, urls[0]);

            if ($(".content-text", creator).val() === "" && $(".content-title", creator).val() === "") {
                $(".content-text", creator).val($("#add-link input").val());
            }

            $(modal).modal("hide");
        } else {
            $(".form-group", modal).addClass("has-error");
        }
    });

    $("body").on("paste", ".creator textarea", function () {
        var element = this;

        setTimeout(function () {
            var text = $(element).val();
            var urls = home.findUrls(text);

            if (urls.length > 0) {
                home.generatedContent($(element).parents(".creator").get(0), urls[0]);
            }

        }, 100);
    });

    $("body").on("click", ".generated .close", function (event) {
        $(event.target).parent().html("");
    });

    $(".contents").sortable({
        items: "> .content-element",
        cancel: "input, textarea, button, select, option, a.btn.dropdown-toggle, .dropdown-menu,a",
        cursor: "move"
    });

    $(".contents").on("sortupdate", function (event, ui) {
        if (this === ui.item.parent()[0]) {
            var a = $(ui.item).data("id");
            var b = $(ui.item).next().data("id");
            var type = $(ui.item).data("type");

            if (a && type) {
                $.ajax(home.path + "content/reorder/" + type + "/" + a + "/" + b)
                .error(
                        function ()
                        {
                            home.modal("content/error");
                        }
                    )
                ;
            }
        }
    });

}());
