'use strict';

/**
 * Template Controller
 */
var TemplateCtrlProto = [
    '$scope',
    '$http',
    '$modal',
    'TemplateFactory',
    function($scope, $http, $modal, TemplateFactory) {
        $scope.templates = [];
        
        $http.get(Routing.generate('innova_path_get_pathtemplates'))
             .then(function(response) {
                 TemplateFactory.setTemplates(response.data);
                 $scope.templates = TemplateFactory.getTemplates();
             });
        
        $scope.edit = function(template) {
            TemplateFactory.setCurrentTemplate(template);
            var modalInstance = $modal.open({
                templateUrl: EditorApp.webDir + 'js/Template/Partial/template-edit.html',
                controller: 'TemplateModalCtrl'
            });
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