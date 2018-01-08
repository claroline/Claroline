
export default class UserService {
  constructor($http, $q, url) {
    this.$http = $http
    this.$q = $q
    this.UrlService = url
  }

  getConnectedUser() {
    const deferred = this.$q.defer()
    this.$http
            .get(
                this.UrlService('api_get_connected_user', {})
            )
            .success(function onSuccess(response) {
              deferred.resolve(response)
            }.bind(this))
            .error(function onError(response) {
              deferred.reject(response)
            })

    return deferred.promise
  }

  connectedUserIsAdmin(user) {
    return user.roles && user.roles.length > 0 && user.roles.find(el => el.name === 'ROLE_ADMIN') !== undefined
  }
}
