(function () {
    'use strict';

    $('.role-delete-btn').on('click', function (event) {
        console.debug(event);
        event.preventDefault();

        if (!$(event.target).hasClass('disabled')) {
            $.ajax({
                url: $(event.target).attr('href'),
                type: 'GET',
                success: function () {
                    $(event.target).parent().parent().remove();
                }
            });
        }
    });
})();