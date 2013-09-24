(function () {
    'use strict';
    
   $('.form-name-widget').on('submit', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget).attr('action');
        var form = e.currentTarget;
        var formData = new FormData(form);
        submitForm(formAction, formData);
   });
   
  $('#create-workspace-widget').on('submit', function (e) {
      e.preventDefault();
      var list = document.getElementById("workspace-widgets");
      var widgetId = list.options[list.selectedIndex].value;
      $.ajax({
            url: Routing.generate('claro_admin_create_workspace_widget', {'widget': widgetId}),
            success: function (data) {
                $('#widget-workspace-table-body').append(data);
            }
        });      
   });
   
  $('#create-desktop-widget').on('submit', function (e) {
      e.preventDefault();
      var list = document.getElementById("desktop-widgets");
      var widgetId = list.options[list.selectedIndex].value;
      $.ajax({
            url: Routing.generate('claro_admin_create_desktop_widget', {'widget': widgetId}),
            success: function (data) {
                $('#widget-desktop-table-body').append(data);
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