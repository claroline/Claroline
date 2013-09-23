(function () {
    "use strict";

    function getResizeDivs(selector)
    {
        var divs = [];
        var line = -1;
        var column = -1;

        var first = $(selector).first().offset();

        $(selector).each(function (index, element) {

            //lines
            if ($(element).offset().left === first.left) {
                first = $(element).offset();
                line++;
                column = -1;
                divs[line] = [];
            }

            //columns
            if ($(element).offset().top === first.top) {
                column++;
                divs[line][column] = [];
                divs[line][column].elements = [];
            }

            divs[line][column].elements[divs[line][column].elements.length] = element;
        });

        return divs;
    }

    function iterateDivs(divs, each) {
        for (var line in divs) {
            if (divs.hasOwnProperty(line)) {
                for (var column in divs[line]) {
                    if (divs[line].hasOwnProperty(column)) {
                        each(divs[line][column].elements, line, column);
                    }
                }
            }
        }

    }

    function resizeDivs()
    {
        var divs = getResizeDivs(".contents .content-element .panel");
        var minHeight = 0;
        var currentline = -1;

        iterateDivs(divs, function (elements) {
            for (var element in elements) {
                if (elements.hasOwnProperty(element)) {
                    $(elements[element]).height("auto");
                }
            }
        });

        iterateDivs(divs, function (elements, line, column) {
            var height = 0; //min height

            if (currentline < line) {
                minHeight = 0;
                currentline++;
            }

            for (var element in elements) {
                if (elements.hasOwnProperty(element)) {
                    height = height + $(elements[element]).outerHeight(true);
                    divs[line][column].height = height;
                }
            }

            if (height > minHeight) {
                minHeight = height;
                divs[line].height = minHeight + 20;
            }
        });

        iterateDivs(divs, function (elements, line, column) {
            for (var element in elements) {
                if (elements.hasOwnProperty(element)) {
                    $(elements[element]).height(
                        $(elements[element]).height() +
                        ((divs[line].height - divs[line][column].height) / divs[line][column].elements.length)
                    );
                }
            }
        });

    }

    var resizeWindow;
    var domChange;

    $(window).on("resize", function () {
        clearTimeout(resizeWindow);
        resizeWindow = setTimeout(resizeDivs, 300);
    });

    $(document).ready(function () {
        resizeDivs();
    });

    $(".contents").bind("DOMSubtreeModified", function () {
        clearTimeout(domChange);
        domChange = setTimeout(resizeDivs, 500);
    });

}());

