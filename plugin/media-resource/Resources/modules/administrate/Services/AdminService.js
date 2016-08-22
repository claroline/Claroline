
/**
* service for administration app
*/
class AdminService {
  constructor($http, $q, url) {
    this.$http = $http
    this.$q = $q
    this.url = url
  }

  save(resource) {
    let deferred = this.$q.defer()
    this.$http
          .post(
              this.url('media_resource_save', { workspaceId: resource.workspaceId, id: resource.id }),
              resource
          )
          .success(function onSuccess(response) {
            deferred.resolve(response)
          }.bind(this))
          .error(function onError(response) {
            deferred.reject(response)
          })

    return deferred.promise
  }

  zip(resource) {
    let deferred = this.$q.defer()
    this.$http
          .post(
              this.url('mediaresource_zip_export', { workspaceId: resource.workspaceId, id: resource.id}),
              resource.regions,
              {responseType:'arraybuffer'}
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

export default AdminService
