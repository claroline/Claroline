
/**
* shared service for user infos
*/
class UserService {
  constructor($http, $q, url) {
    this.$http = $http
    this.$q = $q
    this.url = url
  }

  getConnectedUser() {
    let deferred = this.$q.defer()
    this.$http
          .get(
              this.url('api_get_connected_user', {})
          )
          .success(function onSuccess(response) {
            deferred.resolve(response)
          }.bind(this))
          .error(function onError(response) {
            deferred.reject(response)
          })

    return deferred.promise
  }

  connectedUserIsAdmin(user){
    return user.roles && user.roles.length > 0 && user.roles.find(el => el.name === 'ROLE_ADMIN') !== undefined
  }
}

export default UserService
