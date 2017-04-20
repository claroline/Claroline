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
import cursusDescriptionTemplate from '../Partial/cursus_description_modal.html'

export default class CursusRegistrationSearchCtrl {
  constructor($stateParams, $http, $uibModal) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.initialized = false
    this.cursusList = []
    this.cursusRoots = []
    this.hierarchy = []
    this.search = $stateParams.search
    this.tempSearch = $stateParams.search
    this.selectedCursusId = null
    this.hoveredCursusId = 0

    this.columns = [
      {
        name: 'title',
        prop: 'title',
        headerRenderer: () => {
          return `<b>${Translator.trans('title', {}, 'platform')}</b>`
        },
        cellRenderer: scope => {
          return `
            <a class="pointer-hand"
               ng-class="(crsc.selectedCursusId === ${scope.$row['id']}) ? 'claroline-tag-highlight' : ''"
               ng-click="crsc.getHierarchy(${scope.$row['id']})"
            >
              ${scope.$row['title']}
            </a>
          `
        }
      },
      {
        name: 'code',
        prop: 'code',
        headerRenderer: () => {
          return `<b>${Translator.trans('code', {}, 'platform')}</b>`
        }
      },
      {
        name: 'desciption',
        headerRenderer: function () {
          return `<b>${Translator.trans('description', {}, 'platform')}</b>`
        },
        cellRenderer: scope => {
          const description = scope.$row['description']
          const courseDescription = scope.$row['courseDescription']
          const hasDescription = (description !== null && description !== '') || (courseDescription !== null && courseDescription !== '')
          const descriptionElement = hasDescription ?
            `
              <i class="fa fa-eye pointer-hand"
                 ng-click="crsc.showDescription(${scope.$row['id']})"
              >
              </i>
            ` :
            '-'

          return `<span>${descriptionElement}</span>`
        }
      },
      {
        name: 'cursus',
        prop: 'root',
        headerRenderer: () => {
          return `<b>${Translator.trans('cursus', {}, 'cursus')}</b>`
        },
        cellRenderer: scope => {
          let rootTitle = '-'
          const rootId = scope.$row['root']

          if (this.cursusRoots[rootId]) {
            rootTitle = this.cursusRoots[rootId]['title']
          }

          return `<span>${rootTitle}</span>`
        }
      },
      {
        name: 'type',
        headerRenderer: () => {
          return `<b>${Translator.trans('type', {}, 'platform')}</b>`
        },
        cellRenderer: scope => {
          let type = Translator.trans('cursus', {}, 'cursus')
          const courseId = scope.$row['course']

          if (courseId) {
            type = Translator.trans('course', {}, 'cursus')
          }

          return `<span>${type}</span>`
        }
      }
    ]

    this.dataTableOptions = {
      scrollbarV: false,
      columnMode: 'force',
      headerHeight: 50,
      resizable: true,
      columns: this.columns
    }

    this.initialize()
  }

  initialize() {
    if (!this.initialized) {
      const route = Routing.generate(
        'api_get_datas_for_searched_cursus_registration',
        {search: this.tempSearch}
      )
      this.$http.get(route).then(datas => {

        if (datas['status'] === 200) {
          this.cursusList = datas['data']['searchedCursus']
          this.cursusRoots = datas['data']['roots']
          this.initialized = true
        }
      })
    }
  }

  getCursusInfos(cursusId) {
    let infos = null

    for (let i = 0; i < this.cursusList.length; i++) {
      if (this.cursusList[i]['id'] === cursusId) {
        infos = this.cursusList[i]
        break
      }
    }

    return infos
  }

  getHierarchy(cursusId) {
    this.selectedCursusId = cursusId

    const route = Routing.generate('api_get_datas_for_cursus_hierarchy', {cursus: cursusId})
    this.$http.get(route).then(datas => {
      if (datas['status'] === 200) {
        this.hierarchy = datas['data']
      }
    })
  }

  showDescription(cursusId) {
    const infos = this.getCursusInfos(cursusId)
    let description = infos['courseDescription']

    if (description === null || description === '') {
      description = infos['description']
    }

    if (infos !== null) {
      this.$uibModal.open({
        template: cursusDescriptionTemplate,
        controller: 'CursusDescriptionModalCtrl',
        controllerAs: 'cdmc',
        resolve: {
          title: () => { return infos['title'] },
          description: () => { return description }
        }
      })
    }
  }
}