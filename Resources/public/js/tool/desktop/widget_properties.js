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

   $('body').on('submit', '.form-name-widget', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget).attr('action');
        var form = e.currentTarget;
        var formData = new FormData(form);
        submitForm(formAction, formData);
   });
   
   $('body').on('submit', '#create-widget', function (e) {
      e.preventDefault();
      var list = document.getElementById("widgets");
      var widgetId = list.options[list.selectedIndex].value;
      $.ajax({
            url: Routing.generate('claro_desktop_widget_create', {'widget': widgetId}),
            success: function (data) {
                 $('#widget-table-body').append(data);
            }
        });
   });
   
   $('body').on('click', '.delete-widget', function(e) {
      e.preventDefault();
      $.ajax({
          url: $(e.target).attr('href'),
          success: function (data) {
              $(e.target.parentElement.parentElement).remove();
          }
      });  
   });
   
   var submitForm = function (formAction, formData) {
        $.ajax({
            url: formAction,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function () {
            }
        });
    };
})();

