export default class UserInfoModalController {
  constructor (user, $http) {
    this.$http = $http
    this.user = user
    this.rolesList = user.roles.filter(role => role.type === 1).map(r => this.translate(r.translation_key)).join(', ')
    this.workspaces = []
    this.$http.get(Routing.generate('api_get_user_workspaces', {'user': user.id})).then(d => this.workspaces = d.data)
  }

  translate (key, parameters = {}) {
    return window.Translator.trans(key, parameters, 'platform')
  }
}
