/**
 * Template Factory
 */
(function () {
    'use strict';

    angular.module('TemplateModule').factory('TemplateService', [
        '$http',
        '$q',
        function ($http, $q) {
            var templates = [];
            var currentTemplate = null;

            return {
                /**
                 * Get all available templates
                 */
                all: function () {
                    var deferred = $q.defer();
                    $http.get(Routing.generate('innova_path_template_list'))
                        .success(function (response) {
                            deferred.resolve(response);
                        })
                        .error(function (response) {
                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                /**
                 * Delete a Template
                 * @param template
                 */
                delete: function (template) {
                    var deferred = $q.defer();

                    $http.delete(Routing.generate('innova_path_template_delete', {id: template.id}))
                        .success(function (response) {
                            deferred.resolve(response);
                        })
                        .error(function (response) {
                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                /**
                 *
                 * @param template
                 * @returns TemplateFactory
                 */
                addTemplate: function(template) {
                    templates.push(template);

                    return this;
                },

                /**
                 *
                 * @param template
                 * @returns TemplateFactory
                 */
                replaceTemplate: function(template) {
                    var templateFound = false;
                    for (var i = 0; i < templates.length; i++) {
                        if (templates[i].id == template.id)
                        {
                            templates[i] = template;
                            templateFound = true;
                            break;
                        }
                    }

                    if (!templateFound) {
                        this.addTemplate(template);
                    }

                    return this;
                },

                /**
                 *
                 * @returns Array
                 */
                getTemplates: function() {
                    return templates;
                },

                /**
                 *
                 * @param data
                 * @returns TemplateFactory
                 */
                setTemplates: function(data) {
                    templates = data;

                    return this;
                },

                /**
                 *
                 * @returns object
                 */
                getCurrentTemplate: function() {
                    return currentTemplate;
                },

                /**
                 *
                 * @param data
                 * @returns Template Factory
                 */
                setCurrentTemplate: function(data) {
                    currentTemplate = data;

                    return this;
                }
            };
        }
    ]);
})();