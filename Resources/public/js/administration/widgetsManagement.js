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
    
    $('#widgets-table').on('click', '.edit-widget-btn', function (e) {
        e.preventDefault();
        var widgetId = $(this).data('widget-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_widget_edit_form', {'widget': widgetId}),
            refreshPage,
            function() {}
        );
    });
    
    var refreshPage = function () {
        window.location.reload();
    };
})();