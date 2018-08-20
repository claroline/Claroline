/**
 * Created by panos on 5/30/17.
 */
import userSyncModalTemplate from './user-sync-modal.partial.html'
import { UserSyncModalController } from './user-sync-modal.controller'

export class UserListController {
  constructor(UserListService, $uibModal, $filter) {
    this._UserListService = UserListService
    this._$uibModal = $uibModal
    this._$filter = $filter
    this.initialized = false
    this._lastSearch = {}
  }

  $onInit() {
    this.usersHardLimit = 500
    this.users = []
    this.platformRoles = []
    this.totalUsers = 0
    this.configured = true
    this.totalExternalUsers = 0
    this.syncInProgress = false
    this.fieldNames = [ 'username', 'firstName', 'lastName', 'email', 'administrativeCode' ]
    this.getUsers()
    this.getTotalExternalUsers()
    this.getPlatformRoles()
    this.hasUserConfig()
  }

  openUserSyncModal() {
    let modalInstance = this._$uibModal.open({
      animation: true,
      template: userSyncModalTemplate,
      controller: UserSyncModalController,
      controllerAs: '$ctrl',
      bindToController: true,
      resolve: {
        roles: () => { return this.platformRoles }
      }
    })

    modalInstance.result.then( params => {
      this.synchronizeUsers(1, params.cas, params.role.name)
    })
  }

  hasUserConfig() {
    this._UserListService.hasUserConfig().then(data => {
      this.configured = data
    })
  }

  getTotalExternalUsers() {
    this._UserListService.getTotalExternalUsers().then(data => {
      this.totalExternalUsers = data
    })
  }

  getPlatformRoles() {
    this._UserListService.getPlatformRoles().then(data => {
      this.platformRoles = data
    })
  }

  getUsers(search) {
    search = search || {}
    this._lastSearch = search
    this._UserListService.getUsers(search).then(data => {
      this.totalUsers = data.totalItems
      this.users = data.items
    }, () => {
      this.onAlert({'$alert': {'type' : 'danger', 'msg' : 'user_list_load_error'}})
    }).finally(() => { this.initialized = true })
  }

  synchronizeUsers(batch, cas, role) {
    this.syncInProgress = true
    batch = batch || 1
    this._UserListService.synchronizeUsers(batch, cas, role).then(data => {
      let message = this._$filter('trans')(
        'users_synced_from_to',
        {'from': data.first, 'to': data.last},
        'claro_external_user_group'
      )
      this.onAlert({'$alert': {'type': 'warning', 'msg': message}})
      if (!data.next) {
        this.onAlert({'$alert': {'type': 'success', 'msg': 'user_sync_success'}})
        this.syncInProgress = false
        this.getUsers(this._lastSearch)
      } else {
        this.synchronizeUsers(data.next, cas, role)
      }
    }, () => {
      this.onAlert({'$alert': {'type' : 'danger', 'msg' : 'user_sync_error'}})
      this.syncInProgress = false
      this.getUsers(this._lastSearch)
    })
  }
}

UserListController.$inject = [ 'UserListService', '$uibModal', '$filter' ]