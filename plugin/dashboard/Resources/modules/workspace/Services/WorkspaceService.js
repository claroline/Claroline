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
              this.UrlService('apiv2_user_currentworkspace', {})
          )
          .success(function onSuccess(response) {

            const data = response.data
            //remap attributes because new schema is different
            data.map(el => {
              el.creatorId = el.meta.creator ? el.meta.creator.autoId: 0

              return el
            })

            deferred.resolve(data)
          }.bind(this))
          .error(function onError(response) {
            deferred.reject(response)
          })

    return deferred.promise
  }
}
