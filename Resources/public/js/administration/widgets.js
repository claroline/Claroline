(function () {
    'use strict';
    
   $('body').on('submit', '.form-name-widget', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget).attr('action');
        var form = e.currentTarget;
        var formData = new FormData(form);
        submitForm(formAction, formData);
   });
   
  $('body').on('submit', '#create-workspace-widget', function (e) {
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
   
  $('body').on('submit', '#create-desktop-widget', function (e) {
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