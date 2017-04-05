/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
/*global Translator*/
/*global UserPicker*/

export default class CategoryCreationModalCtrl {
  constructor($rootScope, $http, $uibModalInstance, CategoryService, resourceId, workspaceId, title, callback) {
    this.$rootScope = $rootScope
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.CategoryService = CategoryService
    this.resourceId = resourceId
    this.workspaceId = workspaceId
    this.title = title
    this.callback = callback
    this.category = {
      name: null,
      color: '',
      notifyAddition: true,
      notifyEdition: true,
      notifyRemoval: true,
      notifyPendingComment: true,
      managers: []
    }
    this.categoryErrors = {
      name: null
    }
    this.managers = []
    this._userpickerCallback = this._userpickerCallback.bind(this)
  }

  _userpickerCallback(data) {
    this.managers = data === null ? [] : data
    this.refreshScope()
  }

  submit() {
    this.resetErrors()

    if (!this.category['name']) {
      this.categoryErrors['name'] = Translator.trans('form_not_blank_error', {}, 'clacoform')
    }
    this.category['managers'] = []
    this.managers.forEach(m => {
      this.category['managers'].push(m['id'])
    })
    if (this.isValid()) {
      const url = Routing.generate('claro_claco_form_category_create', {clacoForm: this.resourceId})
      this.$http.post(url, {categoryData: this.category}).then(d => {
        this.callback(d['data'])
        this.$uibModalInstance.close()
      })
    }
  }

  resetErrors() {
    for (const key in this.categoryErrors) {
      this.categoryErrors[key] = null
    }
  }

  isValid() {
    let valid = true

    for (const key in this.categoryErrors) {
      if (this.categoryErrors[key]) {
        valid = false
        break
      }
    }

    return valid
  }

  displayManagers() {
    let value = ''
    let index = 0
    const length = this.managers.length - 1
    this.managers.forEach(u => {
      value += `${u['firstName']} ${u['lastName']}`

      if (index < length) {
        value += ', '
      }
      ++index
    })

    return value
  }

  getSelectedUsersIds() {
    let selectedUsersIds = []
    this.managers.forEach(m => {
      selectedUsersIds.push(m['id'])
    })

    return selectedUsersIds
  }

  openUserPicker() {
    let userPicker = new UserPicker()
    const options = {
      picker_name: 'managers-picker',
      picker_title: Translator.trans('managers_selection', {}, 'clacoform'),
      multiple: true,
      selected_users: this.getSelectedUsersIds(),
      forced_workspaces: [this.workspaceId],
      return_datas: true
    }
    userPicker.configure(options, this._userpickerCallback)
    userPicker.open()
  }

  refreshScope() {
    this.$rootScope.$apply()
  }
}
