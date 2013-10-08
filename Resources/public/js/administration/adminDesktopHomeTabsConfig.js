(function () {
    'use strict';

    $('.hometab-rename-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        alert('rename');
    });

    $('.hometab-visibility-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        alert('visible');

//        var visibilityBtn = $(this);
//        var homeTabConfigId = visibilityBtn.attr('hometab-config-id');
//        var visible = (visibilityBtn.attr('visiblility-value')).trim();
//        var newVisible = (visible === 'visible') ? 'invisible' : 'visible';
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_home_tab_update_visibility',
//                {'homeTabConfigId': homeTabConfigId, 'visible': newVisible}
//            ),
//            type: 'POST',
//            success: function () {
//                if (newVisible === 'visible') {
//                    visibilityBtn.attr('visiblility-value', 'visible')
//                    visibilityBtn.removeClass('icon-eye-close');
//                    visibilityBtn.addClass('icon-eye-open');
//                    visibilityBtn.parent().parent().removeClass('toggle-visible');
//                } else {
//                    visibilityBtn.attr('visiblility-value', 'invisible')
//                    visibilityBtn.removeClass('icon-eye-open');
//                    visibilityBtn.addClass('icon-eye-close');
//                    visibilityBtn.parent().parent().addClass('toggle-visible');
//                }
//            }
//        });
    });

    $('.hometab-lock-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        alert('lock');
    });

    $('.hometab-delete-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        alert('delete');
    });
})();