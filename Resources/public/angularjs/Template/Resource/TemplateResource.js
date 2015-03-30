(function () {
    'use strict';

    angular.module('TemplateModule').factory('TemplateResource', [
        'RestResource',
        function TemplateResource($resource) {
            // Path template follow REST, so the list URL is the base URL for all actions on Templates
            return $resource('innova_path_template_list', { id: '@id' });
        }
    ]);
})();