export default class WorkspaceService{
  constructor($http, $q, url){
    this.$http = $http
    this.$q = $q
    this.UrlService = url
  }

  getConnectedUserWorkspaces() {
    const deferred = this.$q.defer()
    this.$http
          .get(
              this.UrlService('api_get_connected_user_workspaces', {})
          )
          .success(function onSuccess(response) {
            deferred.resolve(response)
          }.bind(this))
          .error(function onError(response) {
            deferred.reject(response)
          })

    return deferred.promise
  }
}
