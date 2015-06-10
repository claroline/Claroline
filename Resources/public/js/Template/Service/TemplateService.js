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

                save: function (template) {
                    var method = null;
                    var route = null;

                    if (template.id) {
                        // Update existing path
                        method = 'PUT';
                        route = Routing.generate('innova_path_template_update', { id: template.id });
                    }
                    else {
                        // Create new path
                        method = 'POST';
                        route = Routing.generate('innova_path_template_create');
                    }

                    $http({
                        method: method,
                        url: route,
                        data: template
                    })
                        .success(function (data) {
                            if ('error' != data) {
                                // No error
                                formTemplate.id = data;
                                TemplateService.replaceTemplate(formTemplate);

                                AlertService.addAlert('success', Translator.trans('path_template_save_success', {}, 'path_wizards'));
                            }
                            else {
                                // Server error while saving
                                AlertService.addAlert('error', Translator.trans('path_template_save_error', {}, 'path_wizards'));
                            }

                            $modalInstance.close();
                        })
                        .error(function (data, status) {
                            AlertService.addAlert('error', Translator.trans('path_template_save_error', {}, 'path_wizards'));
                        });
                },

                create: function (template) {
                    var deferred = $q.defer();

                    $http.post(Routing.generate('innova_path_template_create'), template)
                        .success(function (response) {
                            deferred.resolve(response);
                        })
                        .error(function (response) {
                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                update: function (template) {

                },

                /**
                 * Delete a Template
                 * @param template
                 */
                delete: function (template) {
                    var deferred = $q.defer();

                    $http.delete(Routing.generate('innova_path_template_delete', { id: template.id }))
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