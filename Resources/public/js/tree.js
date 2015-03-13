(function () {
    'use strict';

    // competency tree expansion
    $(document).on('click', 'ul.framework li > i.expand', function () {
        $(this).removeClass('expand')
            .addClass('collapse')
            .removeClass('fa-plus-square-o')
            .addClass('fa-minus-square-o');
        $(this.parentNode).children('ul.children').css('display', 'block');
    });

    // competency tree collapsing
    $(document).on('click', 'ul.framework li > i.collapse', function () {
        $(this).removeClass('collapse')
            .addClass('expand')
            .removeClass('fa-minus-square-o')
            .addClass('fa-plus-square-o');
        $(this.parentNode).children('ul.children').css('display', 'none');
    });
})();