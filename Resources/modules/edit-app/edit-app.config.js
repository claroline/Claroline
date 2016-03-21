EditAppConfig.config.$inject = [ '$routeProvider', 'routeHelperConfigProvider' ];
EditAppConfig.run.$inject = [ '$rootScope', '$alert', 'routeHelper', 'requestHandler', 'website.data' ];

export default class EditAppConfig {

  static config($routeProvider, routeHelperConfigProvider) {
    routeHelperConfigProvider.config.$routeProvider = $routeProvider;
    var resolveAlways = {
      ready: function () {
        return true;
      }
    };
    routeHelperConfigProvider.config.resolveAlways = resolveAlways;
  }

  static run($rootScope, $alert, routeHelper, requestHandler, websiteData) {
    $rootScope.pageLoaded = true;
    $rootScope.optionWindow = websiteData.optionTabs[ 0 ];

    routeHelper.configureRoutes(EditAppConfig.getRoutes(websiteData));
    requestHandler.onRequestSuccess($rootScope, requestSuccessHandler);
    requestHandler.onRequestError($rootScope, requestErrorHandler);

    function requestErrorHandler(rejection) {
      var statusCode = rejection.status;
      if (statusCode != 511 && statusCode != 401 && statusCode != 417) statusCode = 500;
      $alert({
        title: Translator.trans('error', {}, 'icap_website'),
        content: Translator.trans('error_' + statusCode, {}, 'icap_website'),
        placement: 'top',
        type: 'danger',
        duration: 5,
        show: true
      });
    }

    function requestSuccessHandler(response) {
      if (response.config.url.indexOf('.tpl') == -1) {
        $alert({
          title: Translator.trans('success', {}, 'icap_website'),
          content: Translator.trans('success_message', {}, 'icap_website'),
          placement: 'top',
          type: 'success',
          duration: 3,
          show: true
        });
      }
    }
  }

  static getRoutes(websiteData) {
    var routes = [];
    for (var i = 0; i < websiteData.optionTabs.length; i++) {
      var option = websiteData.optionTabs[ i ];
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
}