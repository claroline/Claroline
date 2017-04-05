/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Translator*/
/*global UserPicker*/

export default class EntrySharesManagementModalCtrl {
  constructor($rootScope, $uibModalInstance, NgTableParams, ClacoFormService, EntryService, entry) {
    this.$rootScope = $rootScope
    this.$uibModalInstance = $uibModalInstance
    this.EntryService = EntryService
    this.entry = entry
    this.userId = ClacoFormService.getUserId()
    this.users = []
    this.whiteList = []
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.users}
    )
    this.initialize()
    this._userpickerCallback = this._userpickerCallback.bind(this)
  }

  _userpickerCallback(data) {
    if (data !== null) {
      let ids = []
      data.forEach(u => {
        this.users.push(u)
        ids.push(u['id'])
      })
      this.EntryService.shareEntry(this.entry['id'], ids)
      this.tableParams.reload()
    }
  }

  initialize() {
    this.EntryService.getSharedUsers(this.entry['id']).then(d => {
      JSON.parse(d['data']['users']).forEach(u => this.users.push(u))
      this.whiteList = d['data']['whitelist']
    })
  }

  removeUser(userId) {
    const index = this.users.findIndex(u => u['id'] === userId)

    if (index > -1) {
      this.EntryService.unshareEntry(this.entry['id'], userId)
      this.users.splice(index, 1)
      this.tableParams.reload()
    }
  }

  getSelectedUsersIds() {
    let selectedUsersIds = []
    this.users.forEach(u => {
      selectedUsersIds.push(u['id'])
    })

    return selectedUsersIds
  }

  openUserPicker() {
    let userPicker = new UserPicker()
    const options = {
      picker_name: 'entry-share-picker',
      picker_title: Translator.trans('select_users_to_share', {}, 'clacoform'),
      multiple: true,
      blacklist: this.getSelectedUsersIds(),
      whitelist: this.whitelist,
      //forced_workspaces: [this.workspaceId],
      return_datas: true
    }
    userPicker.configure(options, this._userpickerCallback)
    userPicker.open()
  }

  refreshScope() {
    this.$rootScope.$apply()
  }
}
