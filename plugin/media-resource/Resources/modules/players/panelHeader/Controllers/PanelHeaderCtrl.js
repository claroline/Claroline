
class PanelHeaderCtrl {

  constructor($scope, url, configService, userService) {
    this.configService = configService
    this.userService = userService
    this.urlService = url
    this.$scope = $scope
    this.isAdmin = false
    this.mode = this.configService.getAvailablePlayModes().find(el => el.value === this.resource.options.mode).name
    this.getUser()
  }

  getUser(){
    this.userService.getConnectedUser().then(
      function onSuccess(response) {
        this.isAdmin = this.userService.connectedUserIsAdmin(response)
      }.bind(this),
      function onError() {
      }.bind(this)
    )
  }
}

PanelHeaderCtrl.$inject = [
  '$scope',
  'url',
  'configService',
  'userService'
]
export default PanelHeaderCtrl
