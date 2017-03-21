/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.Sidebar = {
        'displayed': null,
        'resizeWindow': null,
        'scrollUp': null,
        'scrollDown': null
    };

    var sidebar = window.Claroline.Sidebar;


    /**
     * Display a sidebar if is not empty.
     * Add icon, scroll buttons and push classes to the body and the top bars.
     *
     * @param side The sideof the sibebar that can be right or left.
     *
     */
    sidebar.initialize = function (side) {
        var sidebar = $('#' + side + '-bar');
        if (sidebar && sidebar.html() && sidebar.html().replace(/^\s+/g, '').replace(/\s+$/g, '') !== '') {

            sidebar.removeClass('hide');

            var hasIcon = false;
            $('.list-group-item.disabled', sidebar).children().each(function () {
                // Search in sidebar header if dev has defined an icon for his bar
                var className = $(this).prop('class');
                if (className.match(/^fa-/) !== null) {
                    hasIcon = true;
                    return;
                }
            });

            if (!hasIcon) {
                // Add little arrow to header
                var icon = (side === 'left') ? 'right' : 'left';
                var header = '<i class="fa fa-caret-' + icon + '"></i>' +
                             $('.list-group-item.disabled', sidebar).html();
                $('.list-group-item.disabled', sidebar).html(header);
            }

            sidebar.append('<div class="scroll-up hide" aria-hidden="true">' +
                            '<i class="fa fa-angle-double-up"></i></div>' +
                            '<div class="scroll-down hide" aria-hidden="true">' +
                            '<i class="fa fa-angle-double-down"></i></div>'
            );

            $('body').addClass(side + '-bar-push');
            $('#top_bar').addClass(side + '-bar-push');
            $('.impersonalitation > .navbar-fixed-top').addClass(side + '-bar-push');
        }
    };

    /**
     * Display sidebar
     *
     * @param event The mouse event
     * @param element The sidebar html element
     *
     */
    sidebar.display = function (event, element) {
        // if correct bug chrome inside select autside element
        if (event.clientX !== 0 && event.clientY !== 0) {
            clearTimeout(sidebar.displayed);
            sidebar.displayed = setTimeout(function () {
                $(element).animate({width: '400px'}, 300);
            }, 200);
        }
    };

    /**
     * Hide sidebar
     *
     * @param element The sidebar html element
     *
     */
    sidebar.hide = function (element) {
        clearTimeout(sidebar.displayed);
        sidebar.displayed = setTimeout(function () {
            /*$('.in', element).each(function () {
                $(this).removeClass('in');
                $(this).addClass('collapse');
            });*/
            $(element).animate({width: '40px'}, 300);
        }, 200);
    };

    /**
     * Check window height and sidebar menu height in order to display or hide scroll buttons.
     */
    sidebar.checkHeight = function ()
    {
        var windowHeight = $(window).height();
        $('.sidebar .scroll-down, .sidebar .scroll-up').addClass('hide');
        $('.sidebar .list-group').css('top', '0');

        $('.sidebar').each(function () {
            var element = this;
            var menuHeight = $('.list-group.menu', element).first().outerHeight(true);
            if (menuHeight > windowHeight - 50) {
                $('.scroll-down', element).removeClass('hide');
            }
        });
    };

    /**
     * Change the relative position of a menu inside of a side bar
     *
     * @param element Html element of a scroll button
     * @param move The size in pixels to move, that can be positive or negative
     *
     */
    sidebar.scroll = function (element, move) {
        var windowHeight = $(window).height();
        var sidebar = $(element).parents('.sidebar');
        var menu = $('.list-group.menu', sidebar).first();
        var height = menu.outerHeight(true);
        var current = parseInt(menu.css('top').replace('px', ''));

        if ((move > 0 && current + move <= 0) || (move < 0 && current >= windowHeight - height - 43)) {
            menu.css('top', (current +  move) + 'px');

            if (current + move < 0) {
                $('.scroll-up', sidebar).removeClass('hide');
            } else {
                $('.scroll-up', sidebar).addClass('hide');
            }

            if (current + move >= windowHeight - height - 43) {
                $('.scroll-down', sidebar).removeClass('hide');
            } else {
                $('.scroll-down', sidebar).addClass('hide');
            }
        }
    };

    sidebar.initialize('left');
    sidebar.initialize('right');

    $(window).on('resize', function () {
        clearTimeout(sidebar.resizeWindow);
        sidebar.resizeWindow = setTimeout(sidebar.checkHeight, 500);
    })
    .load(function () {
        clearTimeout(sidebar.resizeWindow);
        sidebar.resizeWindow = setTimeout(sidebar.checkHeight, 500);
    });

    $('body').on('mouseenter', '.sidebar', function (event) {
        sidebar.display(event, this);
    })
    .on('mouseleave', '.sidebar', function () {
        sidebar.hide(this);
    })
    .on('mouseenter', '.sidebar .scroll-down', function () {
        var element = this;
        clearInterval(sidebar.scrollUp);
        clearInterval(sidebar.scrollDown);
        sidebar.scrollDown = setInterval(function () {
            sidebar.scroll(element, -2);
        }, 10);
    })
    .on('mouseenter', '.sidebar .scroll-up', function () {
        var element = this;
        clearInterval(sidebar.scrollUp);
        clearInterval(sidebar.scrollDown);
        sidebar.scrollUp = setInterval(function () {
            sidebar.scroll(element, 2);
        }, 10);
    })
    .on('mouseleave', '.sidebar, .sidebar .scroll-up, .sidebar .scroll-down', function () {
        clearInterval(sidebar.scrollUp);
        clearInterval(sidebar.scrollDown);
    });
})();
