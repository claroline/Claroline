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
import sessionsTemplate from '../Partial/cursus_registration_sessions_modal.html'
import groupsListRegistrationTemplate from '../Partial/cursus_groups_list_registration_modal.html'
import groupUnregistrationTemplate from '../Partial/cursus_group_unregistration_modal.html'
import groupsUnregistrationTemplate from '../Partial/cursus_groups_unregistration_modal.html'
import userUnregistrationTemplate from '../Partial/cursus_user_unregistration_modal.html'
import usersUnregistrationTemplate from '../Partial/cursus_users_unregistration_modal.html'

export default class CursusRegistrationManagementCtrl {
  constructor($stateParams, $http, $uibModal) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.unlockedCursusTxt = ''
    this.usersIdsTxt
    this.currentCursusId = $stateParams.cursusId
    this.hierarchy = []
    this.lockedHierarchy = []
    this.unlockedCursus = []
    this.cursusGroups = []
    this.cursusUsers = []
    this.selectedUsers = {}
    this.selectedCursusGroups = {}
    this.allUsers = false
    this.allGroups = false

    this.usersColumns = [
      {
        name: 'checkboxes',
        headerRenderer: () => {
          return `
            <span>
              <input type="checkbox" ng-click="crmc.toggleAllUsers()">
            </span>
          `
        },
        cellRenderer: scope => {
          return `
            <span>
              <input type="checkbox" ng-model="crmc.selectedUsers[${scope.$row['userId']}]">
            </span>
          `
        }
      },
      {
        name: 'firstName',
        prop: 'firstName',
        headerRenderer: () => {
          return `<b>${Translator.trans('first_name', {}, 'platform')}</b>`
        }
      },
      {
        name: 'lastName',
        prop: 'lastName',
        headerRenderer: () => {
          return `<b>${Translator.trans('last_name', {}, 'platform')}</b>`
        }
      },
      {
        name: 'username',
        prop: 'username',
        headerRenderer: () => {
          return `<b>${Translator.trans('username', {}, 'platform')}</b>`
        }
      },
      {
        name: 'registration_date',
        prop: 'registrationDate',
        headerRenderer: () => {
          return `<b>${Translator.trans('registration_date', {}, 'cursus')}</b>`
        },
        cellRenderer: function (scope) {
          return `<span>${scope.$row['registrationDate']['date']}</span>`
        }
      },
      {
        name: 'actions',
        headerRenderer: () => {
          return `
            <button class="btn btn-default btn-sm"
                    ng-click="crmc.unregisterSelectedUsers()"
                    ng-disabled="!crmc.isUserSelected()"
            >
              ${Translator.trans('unregister_selected_users', {}, 'cursus')}
            </button>
          `
        },
        cellRenderer: () => {
          return `
            <button class="btn btn-danger btn-sm"
                    ng-click="crmc.unregisterUser($row)"
            >
              ${Translator.trans('unregister', {}, 'cursus')}
            </button>
          `
        }
      }
    ]

    this.groupsColumns = [
      {
        name: 'checkboxes',
        headerRenderer: () => {
          return `
            <span>
              <input type="checkbox" ng-click="crmc.toggleAllGroups()">
            </span>
          `
        },
        cellRenderer: scope => {
          return `
            <span>
              <input type="checkbox" ng-model="crmc.selectedCursusGroups[${scope.$row['id']}]">
            </span>
          `
        }
      },
      {
        name: 'name',
        prop: 'groupName',
        headerRenderer: () => {
          return `<b>${Translator.trans('name', {}, 'platform')}</b>`
        }
      },
      {
        name: 'registration_date',
        prop: 'registrationDate',
        headerRenderer: () => {
          return `<b>${Translator.trans('registration_date', {}, 'cursus')}</b>`
        },
        cellRenderer: function (scope) {
          return `<span>${scope.$row['registrationDate']['date']}</span>`
        }
      },
      {
        name: 'actions',
        headerRenderer: () => {
          return `
            <button class="btn btn-default btn-sm"
                    ng-click="crmc.unregisterSelectedGroups()"
                    ng-disabled="!crmc.isGroupSelected()"
            >
              ${Translator.trans('unregister_selected_groups', {}, 'cursus')}
            </button>
          `
        },
        cellRenderer: () => {
          return `
            <button class="btn btn-danger btn-sm"
                    ng-click="crmc.unregisterGroup($row)"
            >
              ${Translator.trans('unregister', {}, 'cursus')}
            </button>
          `
        }
      }
    ]

    this.dataGroupsTableOptions = {
      scrollbarV: false,
      columnMode: 'force',
      headerHeight: 50,
      selectable: true,
      multiSelect: true,
      checkboxSelection: true,
      resizable: true,
      columns: this.groupsColumns
    }

    this.dataUsersTableOptions = {
      scrollbarV: false,
      columnMode: 'force',
      headerHeight: 50,
      selectable: true,
      multiSelect: true,
      checkboxSelection: true,
      resizable: true,
      columns: this.usersColumns
    }

    this.userPickerCallBack = this.userPickerCallBack.bind(this)
    this.removeCursusGroup = this.removeCursusGroup.bind(this)
    this.removeCursusGroups = this.removeCursusGroups.bind(this)
    this.removeCursusUser = this.removeCursusUser.bind(this)
    this.removeCursusUsers = this.removeCursusUsers.bind(this)
    this.initialize()
  }

  userPickerCallBack(datas) {
    if (datas === null) {
      this.usersIdsTxt = '0'
    } else {
      this.usersIdsTxt = ''

      for (let i = 0; i < datas.length; i++) {
        this.usersIdsTxt += datas[i]['id'] + ','
      }
      const length = this.usersIdsTxt.length

      if (length > 0) {
        this.usersIdsTxt = this.usersIdsTxt.substr(0, length - 1)
      }
    }

    this.$uibModal.open({
      template: sessionsTemplate,
      controller: 'CursusRegistrationSessionsModalCtrl',
      controllerAs: 'crsmc',
      resolve: {
        cursusId: () => {
          return this.currentCursusId},
        sourceId: () => {
          return this.usersIdsTxt},
        sourceType: () => {
          return 'user'},
        cursusIdsTxt: () => {
          return this.unlockedCursusTxt}
      }
    })
  }

  registerGroups() {
    this.$uibModal.open({
      template: groupsListRegistrationTemplate,
      controller: 'CursusGroupsListRegistrationModalCtrl',
      controllerAs: 'cglrmc',
      resolve: {
        cursusId: () => {
          return this.currentCursusId},
        cursusIdsTxt: () => {
          return this.unlockedCursusTxt}
      }
    })
  }

  registerUsers() {
    let usersIds = []

    for (let i = 0; i < this.cursusUsers.length; i++) {
      usersIds.push(this.cursusUsers[i]['userId'])
    }
    let userPicker = new UserPicker()
    let config = {
      picker_name: 'cursus-registration-users-picker',
      picker_title: Translator.trans('register_users_to_cursus', {}, 'cursus'),
      multiple: true,
      blacklist: usersIds,
      return_datas: true,
      attach_name: false,
      filter_admin_orgas: true
    }
    userPicker.configure(config, this.userPickerCallBack)
    userPicker.open()
  }

  unregisterGroup(group) {
    const cursusGroupId = group.id
    const groupName = group.groupName
    this.$uibModal.open({
      template: groupUnregistrationTemplate,
      controller: 'CursusGroupUnregistrationModalCtrl',
      controllerAs: 'cgumc',
      resolve: {
        cursusGroupId: () => {
          return cursusGroupId},
        groupName: () => {
          return groupName},
        callBack: () => {
          return this.removeCursusGroup}
      }
    })
  }

  unregisterSelectedGroups() {
    let cursusGroupsIdsTxt = ''

    for (let cursusGroupId in this.selectedCursusGroups) {
      if (this.selectedCursusGroups[cursusGroupId]) {
        cursusGroupsIdsTxt += cursusGroupId + ','
      }
    }
    const length = cursusGroupsIdsTxt.length

    if (length > 0) {
      cursusGroupsIdsTxt = cursusGroupsIdsTxt.substr(0, length - 1)
    }
    this.$uibModal.open({
      template: groupsUnregistrationTemplate,
      controller: 'CursusGroupsUnregistrationModalCtrl',
      controllerAs: 'cgumc',
      resolve: {
        cursusGroupsIdsTxt: () => {
          return cursusGroupsIdsTxt},
        callBack: () => {
          return this.removeCursusGroups}
      }
    })
  }

  removeCursusGroup(cursusGroupId) {
    for (let i = 0; i < this.cursusGroups.length; i++) {
      if (this.cursusGroups[i]['id'] === cursusGroupId) {
        this.cursusGroups.splice(i, 1)
        break
      }
    }
    this.updateCursusUsers()
  }

  removeCursusGroups(cursusGroupIds) {
    for (let i = this.cursusGroups.length - 1; i >= 0; i--) {
      if (cursusGroupIds.indexOf(this.cursusGroups[i]['id']) >= 0) {
        this.cursusGroups.splice(i, 1)
      }
    }
    this.updateCursusUsers()
  }

  removeCursusUsers(userIds) {
    for (let i = this.cursusUsers.length - 1; i >= 0; i--) {
      if (userIds.indexOf(this.cursusUsers[i]['userId']) >= 0) {
        this.cursusUsers.splice(i, 1)
      }
    }
  }

  unregisterUser(user) {
    const cursusUserId = user.id
    const name = `${user['firstName']} ${user['lastName']} (${user['username']})`
    this.$uibModal.open({
      template: userUnregistrationTemplate,
      controller: 'CursusUserUnregistrationModalCtrl',
      controllerAs: 'cuumc',
      resolve: {
        cursusUserId: () => {
          return cursusUserId},
        name: () => {
          return name},
        callBack: () => {
          return this.removeCursusUser}
      }
    })
  }

  unregisterSelectedUsers() {
    let idsTxt = ''

    for (let userId in this.selectedUsers) {
      if (this.selectedUsers[userId]) {
        idsTxt += userId + ','
      }
    }
    const length = idsTxt.length

    if (length > 0) {
      idsTxt = idsTxt.substr(0, length - 1)
    }
    this.$uibModal.open({
      template: usersUnregistrationTemplate,
      controller: 'CursusUsersUnregistrationModalCtrl',
      controllerAs: 'cuumc',
      resolve: {
        cursusId: () => {
          return this.currentCursusId},
        usersIdsTxt: () => {
          return idsTxt},
        callBack: () => {
          return this.removeCursusUsers}
      }
    })
  }

  removeCursusUser(cursusUserId) {
    for (let i = 0; i < this.cursusUsers.length; i++) {
      if (this.cursusUsers[i]['id'] === cursusUserId) {
        this.cursusUsers.splice(i, 1)
        break
      }
    }
  }

  isGroupSelected() {
    let selected = false

    for (let cursusGroupId in this.selectedCursusGroups) {
      if (this.selectedCursusGroups[cursusGroupId]) {
        selected = true
        break
      }
    }

    return selected
  }

  isUserSelected() {
    let selected = false

    for (let userId in this.selectedUsers) {
      if (this.selectedUsers[userId]) {
        selected = true
        break
      }
    }

    return selected
  }

  toggleAllGroups() {
    this.allGroups = !this.allGroups

    for (let cursusGroupId in this.selectedCursusGroups) {
      this.selectedCursusGroups[cursusGroupId] = this.allGroups
    }
  }

  toggleAllUsers() {
    this.allUsers = !this.allUsers

    for (let userId in this.selectedUsers) {
      this.selectedUsers[userId] = this.allUsers
    }
  }

  updateCursusUsers() {
    const route = Routing.generate(
      'api_get_cursus_users_for_cursus_registration',
      {cursus: this.currentCursusId}
    )
    this.$http.get(route).then(datas => {
      if (datas['status'] === 200) {
        this.cursusUsers = datas['data']
      }
    })
  }

  initialize() {
    const route = Routing.generate('api_get_datas_for_cursus_registration', {cursus: this.currentCursusId})
    this.$http.get(route).then(datas => {
      const data = datas['data']
      this.hierarchy = data['hierarchy']
      this.lockedHierarchy = data['lockedHierarchy']
      this.unlockedCursus = data['unlockedCursus']
      this.cursusGroups = data['cursusGroups']
      this.cursusUsers = data['cursusUsers']

      for (let i = 0; i < this.unlockedCursus.length; i++) {
        this.unlockedCursusTxt += this.unlockedCursus[i]

        if (i < this.unlockedCursus.length - 1) {
          this.unlockedCursusTxt += ','
        }
      }

      for (let i = 0; i < this.cursusGroups.length; i++) {
        let cursusGroupId = this.cursusGroups[i]['id']
        this.selectedCursusGroups[cursusGroupId] = false
      }

      for (let i = 0; i < this.cursusUsers.length; i++) {
        let userId = this.cursusUsers[i]['userId']
        this.selectedUsers[userId] = false
      }
    })
  }
}
