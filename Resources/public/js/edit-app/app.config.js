(function() {
    'use strict';
    var editApp = angular.module('app');

    editApp.config(['$routeProvider', 'routeHelperConfigProvider',
        function ($routeProvider, routeHelperConfigProvider) {
            routeHelperConfigProvider.config.$routeProvider = $routeProvider;
            var resolveAlways = {
                ready: function () {
                    return true;
                }
            };
            routeHelperConfigProvider.config.resolveAlways = resolveAlways;
        }
    ]);

    editApp.run(['$rootScope', '$alert', 'routeHelper', 'requestHandler', 'website.data',
        function($rootScope, $alert, routeHelper, requestHandler, websiteData){
            $rootScope.pageLoaded = true;
            $rootScope.optionWindow = websiteData.optionTabs[0];

            routeHelper.configureRoutes(getRoutes(websiteData));
            requestHandler.onRequestSuccess($rootScope, requestSuccessHandler);
            requestHandler.onRequestError($rootScope, requestErrorHandler);

            function requestErrorHandler(rejection) {
                var statusCode = rejection.status;
                if(statusCode!=511 && statusCode!=401 && statusCode!=417) statusCode = 500;
                $alert({
                    title: Translator.get('icap_website:error'),
                    content: Translator.get('icap_website:error_'+statusCode),
                    placement: 'top',
                    type: 'danger',
                    duration: 5,
                    show: true
                });
            }

            function requestSuccessHandler(response) {
                if(response.config.url.indexOf('.tpl')==-1){
                    $alert({
                        title: Translator.get('icap_website:success'),
                        content: Translator.get('icap_website:success_message'),
                        placement: 'top',
                        type: 'success',
                        duration: 3,
                        show: true
                    });
                }
            }
        }
    ]);

    function getRoutes(websiteData) {
        var routes = [];
        for (var i = 0; i < websiteData.optionTabs.length; i++) {
            var option = websiteData.optionTabs[i];
            routes.push(
                {
                    url: '/' + option,
                    config: {
                        option: option
                    }
                }
            );
        }

        return routes;
    }
})();