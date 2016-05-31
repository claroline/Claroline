export default class UserInfoModalController {
  constructor (user) {
    this.user = user
    this.rolesList = user.roles.filter(role => role.type === 1).map(r => this.translate(r.translation_key)).join(', ')
  }

  translate (key, parameters = {}) {
    return window.Translator.trans(key, parameters, 'platform')
  }
}
