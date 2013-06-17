(function () {
    "use strict";

    var homePath = $("#homePath").html(); //global

    function save(id)
    {
        var ready = $.Deferred();

        var name = $("#theme-name").val();

        var variables = "";

        $(".theme-value input").each(function () {
            variables +=  $("code", this.parentNode.parentNode).html() + ": " + this.value + ";\n";
        });

        $.post(homePath + "admin/theme/build", {
            "theme-id": id,
            "name": name,
            "variables": variables
        })
        .done(
            function (data) {
                if (!isNaN(data) && data !== "") {

                    if (name === "" || name === undefined) {
                        $("#theme-name").val("Theme" + data);
                    }

                    ready.resolve(data);

                } else {
                    modal("admin/theme/error");
                }
            }
        )
        .error(
            function () {
                modal("admin/theme/error");
            }
        );

        return ready;
    }

    function modal(url, name, id)
    {
        $(".modal").modal("hide");

        name = typeof(name) !== "undefined" ? name : null;
        id = typeof(id) !== "undefined" ? id : null;

        $.ajax(homePath + url)
        .done(
            function (data)
            {
                var modal = document.createElement("div");
                modal.className = "modal hide fade";

                if (name) {
                    modal.setAttribute("id", name);
                }

                if (id) {
                    $(modal).data("id", id);
                }

                modal.innerHTML = data;

                $(modal).appendTo("body");

                $(modal).modal("show");

                $(modal).on("hidden", function () {
                    $(this).remove();
                });

            }
        );
    }

    $("body").on("click", ".theme-generator .btn.dele", function () {

        modal("admin/theme/confirm", "delete-theme", $(this).data("id"));
    });

    $("body").on("click", ".#delete-theme .btn.delete", function () {
        var id = $("#delete-theme").data("id");

        $.ajax(homePath + "admin/theme/delete/" + id)
        .done(
            function (data) {
                if (data === "true") {
                    window.location = homePath + "admin/theme/list";
                } else {
                    alert(data);
                }
            }
        )
        .error(
            function () {
                modal("admin/theme/error");
            }
        );

    });

    $("body").on("click", ".theme-generator .btn.save", function () {
        save($(this).data("id"))
        .done(
            function () {
                window.location = homePath + "admin/theme/list";
            }
        );
    });

    $("body").on("click", ".theme-generator .btn.preview", function () {
        save($(this).data("id"))
        .done(
            function (data) {
                window.open(homePath + "admin/theme/preview/" + data, "claroline-preview");
            }
        );
    });


    $(".picker").colorpicker().on("changeColor", function () {
        $(".theme-swatch span", this.parentNode.parentNode).css("background", this.value);
    });

}());
