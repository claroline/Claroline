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
import sessionsTemplate from '../Partial/cursus_registration_sessions_modal.html'

export default class CursusGroupsListRegistrationModalCtrl {
        
  constructor($http, $uibModal, $uibModalInstance, cursusId, cursusIdsTxt) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.$uibModalInstance = $uibModalInstance
    this.cursusId = cursusId
    this.cursusIdsTxt = cursusIdsTxt
    this.search = ''
    this.tempSearch = ''
    this.groups = []

    this.groupsColumns = [
      {
        name: 'name',
        prop: 'name',
        headerRenderer: () => {
          return `<b>${Translator.trans('name', {}, 'platform')}</b>`
        }
      },
      {
        name: 'actions',
        headerRenderer: () => {
          return `<b>${Translator.trans('actions', {}, 'platform')}</b>`
        },
        cellRenderer: scope => {
          return `
            <button class="btn btn-success btn-sm"
                    ng-click="cglrmc.selectGroupForSessionsValidation(${scope.$row['id']})"
            >
              ${Translator.trans('register', {}, 'cursus')}
            </button>
          `
        }
      }
    ]

    this.dataTableOptions = {
      scrollbarV: false,
      columnMode: 'force',
      headerHeight: 50,
      selectable: true,
      multiSelect: true,
      checkboxSelection: true,
      columns: this.groupsColumns
    }

    this.getUnregisteredGroups()
  }

  getUnregisteredGroups() {
    const route = (this.search === '') ?
      Routing.generate('api_get_unregistered_cursus_groups', {cursus: this.cursusId}) :
      Routing.generate('api_get_searched_unregistered_cursus_groups', {cursus: this.cursusId, search: this.search})
    this.$http.get(route).then(datas => {
      if (datas['status'] === 200) {
        this.groups = datas['data'].groups
      }
    })
  }

  selectGroupForSessionsValidation(groupId) {
    this.closeModal()
    this.$uibModal.open({
      template: sessionsTemplate,
      controller: 'CursusRegistrationSessionsModalCtrl',
      controllerAs: 'crsmc',
      resolve: {
        cursusId: () => { return this.cursusId },
        sourceId: () => { return groupId },
        sourceType: () => { return 'group' },
        cursusIdsTxt: () => { return this.cursusIdsTxt }
      }
    })
  }

  closeModal() {
    this.$uibModalInstance.close()
  }

  searchGroups() {
    this.search = this.tempSearch
    this.getUnregisteredGroups()
  }
}
