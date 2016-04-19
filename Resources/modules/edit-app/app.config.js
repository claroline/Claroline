export default class EditAppConfig {

  static config($routeProvider, routeHelperConfigProvider) {
    routeHelperConfigProvider.config = {
      $routeProvider: $routeProvider,
      resolveAlways: { ready: () => {return true} },
      defaultPath: '/structure',
      rootChangeSuccess: ($rootScope, current) => {$rootScope.optionWindow = current.option}
    }
  }

  static run($rootScope, routeHelper, requestHandler, websiteData) {
    $rootScope.pageLoaded = true
    $rootScope.optionWindow = websiteData.optionTabs[ 0 ]

    routeHelper.configureRoutes(EditAppConfig.getRoutes(websiteData))
    requestHandler.onRequestSuccess($rootScope, EditAppConfig.requestSuccessHandler)
    requestHandler.onRequestError($rootScope, EditAppConfig.requestErrorHandler)

  }

  static getRoutes(websiteData) {
    var routes = []
    for (var i = 0; i < websiteData.optionTabs.length; i++) {
      var option = websiteData.optionTabs[ i ]
      routes.push(
        {
          url: '/' + option,
          config: {
            option: option
          }
        }
      )
    }

    return routes
  }

  static requestSuccessHandler(response) {
    if (response.config.url.indexOf('.partial') == -1 && response.config.url.indexOf('.tpl') == -1 && response.config.url.indexOf('template') == -1) {
      response.alert.new({
        title: window.Translator.trans('success', {}, 'icap_website'),
        content: window.Translator.trans('success_message', {}, 'icap_website'),
        type: 'success',
        duration: 3000
      })
    }
  }

  static requestErrorHandler(rejection) {
    let statusCode = rejection.status
    if (statusCode != 511 && statusCode != 401 && statusCode != 417) statusCode = 500
    rejection.alert.new({
      title: window.Translator.trans('error', {}, 'icap_website'),
      content: window.Translator.trans('error_' + statusCode, {}, 'icap_website'),
      type: 'danger',
      duration: 5000
    })
  }
}

EditAppConfig.config.$inject = [ '$routeProvider', 'routeHelperConfigProvider' ]
EditAppConfig.run.$inject = [ '$rootScope', 'routeHelper', 'requestHandler', 'website.data' ]
