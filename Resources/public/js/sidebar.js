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

    window.ClarolineSidebar = {
        displayed: {
            left: null,
            right: null
        },
        
        getSide: function (element) {
            return this.isLeft(element) ? 'left' : 'right';
        },
        
        isLeft: function (element) {
            return 'left-bar' === $(element).prop('id');
        },
        
        initialize: function (element) {
            var side = this.getSide(element);
            
            var $sidebar = $('#' + side + '-bar');
            if ($sidebar.html().replace(/^\s+/g, '').replace(/\s+$/g, '') !== '') {
                // Sidebar is not empty => need to display it
                $sidebar.parent().removeClass('hide');
                
                var hasIcon = false;
                $sidebar.find('.list-group-item.disabled').children().each(function (index) {
                    // Search in sidebar header if dev has defined an icon for his bar
                    var className = $(this).prop('class');
                    if (className.match(/^icon-/) != null) {
                        // Icon found
                        hasIcon = true;
                        
                        return false; // Break the loop
                    }
                });
                
                if (!hasIcon) {
                    // Add little arrow to header
                    var otherSide = 'left' === side ? 'right' : 'left';
                    var header = '<i class="icon-caret-' + otherSide + '"></i>' + $sidebar.find('.list-group-item.disabled').html();
                    $sidebar.find('.list-group-item.disabled').html(header);
                }
                
                $('body').addClass(side + '-bar-push');
                $('#top_bar').addClass(side + '-bar-push');
                $('.impersonalitation > .navbar-fixed-top').addClass(side + '-bar-push');
            }
        },
        
        display: function(event, element) {
            var side = this.getSide(element);
            
            // if correct bug chrome inside select autside element
            if (event.clientX !== 0 && event.clientY !== 0) {
                clearTimeout(window.ClarolineSidebar.displayed[side]);
                window.ClarolineSidebar.displayed[side] = setTimeout(function () {
                    $(element).animate({width: '400px'}, 300);
                }, 200);
            }
        },
        
        hide: function(element) {
            var side = this.getSide(element);
            
            clearTimeout(window.ClarolineSidebar.displayed[side]);
            window.ClarolineSidebar.displayed[side] = setTimeout(function () {
                $('.in', element).each(function () {
                    $(this).removeClass('in');
                    $(this).addClass('collapse');
                });

                $(element).animate({width: '40px'}, 300);
            }, 200);
        }
    };
    
    window.ClarolineSidebar.initialize('#left-bar');
    window.ClarolineSidebar.initialize('#right-bar');
    
    $('body').on('mouseenter', '.sidebar', function (event) {
        // Display sidebar on mouse hover
        window.ClarolineSidebar.display(event, this);
    })
    .on('mouseleave', '.sidebar', function () {
        // Hide sidebar on mouse leave
        window.ClarolineSidebar.hide(this);
    });

}());
