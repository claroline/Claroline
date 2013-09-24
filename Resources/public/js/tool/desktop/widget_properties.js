(function () {
    'use strict';

   $('.form-name-widget').on('submit', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget).attr('action');
        var form = e.currentTarget;
        var formData = new FormData(form);
        submitForm(formAction, formData);
   });
   
   $('#create-widget').on('submit', function (e) {
      e.preventDefault();
      var list = document.getElementById("widgets");
      var widgetId = list.options[list.selectedIndex].value;
      $.ajax({
            url: Routing.generate('claro_desktop_widget_create', {'widget': widgetId}),
            success: function () {
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

