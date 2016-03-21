/**
 * Created by ptsavdar on 15/03/16.
 */
class ViewAppConfig {
  static run($rootScope) {
    $rootScope.pageLoaded = true;
  }
}

ViewAppConfig.run.$inject = [ '$rootScope' ];

export default ViewAppConfig