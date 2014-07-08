/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The height of a content in home page must be resized when:
 *
 * reorder content
 * create content
 * delete content
 * update content
 * resize content
 * resize window
 *
 */
(function () {
    'use strict';

    var selector = '.contents .content-element > .panel';

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
        $.when($(selector).height('auto')).done(function () {
            var divs = getResizeDivs(selector);
            var minHeight = 0;
            var currentline = -1;

            iterateDivs(divs, function (elements, line, column) {
                var height = 0; //min height

                if (currentline < line) {
                    minHeight = 0;
                    currentline++;
                }

                for (var element in elements) {
                    if (elements.hasOwnProperty(element)) {
                        height = height + $(elements[element]).outerHeight(true) - 2;
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
        });
    }

    var resizeWindow;
    var domChange;

    $(window).on('resize', function () {
        clearTimeout(resizeWindow);
        resizeWindow = setTimeout(resizeDivs, 500);
    })
    .load(function () {
        clearTimeout(resizeWindow);
        resizeWindow = setTimeout(resizeDivs, 500);
    });

    $(document).ready(function () {
        clearTimeout(resizeWindow);
        resizeWindow = setTimeout(resizeDivs, 500);
    });

    // use custom ContentModified instead DOMSubtreeModified
    // example $('.contents').trigger('ContentModified');
    $('.contents').bind('ContentModified', function () {
        clearTimeout(domChange);
        domChange = setTimeout(resizeDivs, 500);
    });

}());
