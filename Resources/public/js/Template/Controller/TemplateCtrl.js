'use strict';

/**
 * Template Controller
 */
var TemplateCtrlProto = [
    '$scope',
    '$http',
    '$dialog',
    'TemplateFactory',
    'AlertFactory',
    'ClipboardFactory',
    function($scope, $http, $dialog, TemplateFactory, AlertFactory, ClipboardFactory) {
        $scope.templates = [];
         
        $http
             .get('../api/index.php/path/templates.json')
             .then(function(response) {
                 TemplateFactory.setTemplates(response.data);
                 $scope.templates = TemplateFactory.getTemplates();
             });

        $scope.copyToClipboard = function(template) {
            ClipboardFactory.copy(template, true);
        };
        
        $scope.edit = function(template) {
            TemplateFactory.setCurrentTemplate(template);
            
            var dialogOptions = {
                backdrop: true,
                keyboard: true,
                backdropClick: true
            };
            
            var d = $dialog.dialog(dialogOptions);
            d.open('partials/modals/template-edit.html', 'TemplateModalCtrl');
        };
        
        $scope.delete = function(template, id) {
            $http
                .delete('../api/index.php/path/templates/' + template.id + '.json')
                .then( function(response) {
                    $scope.templates.splice(id, 1);
                });
        };
    }
];