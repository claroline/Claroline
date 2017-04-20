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

export default class CursusCreationModalCtrl {
  constructor($http, $uibModalInstance, FormBuilderService, CourseService, title, parentId, callback) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.FormBuilderService = FormBuilderService
    this.title = title
    this.parentId = parentId
    this.callback = callback
    this.cursus = {
      title: null,
      code: null,
      description: '',
      icon: '',
      workspace: null,
      blocking: false,
      color: '',
      organizations: []
    }
    this.cursusErrors = {
      title: null,
      code: null,
      organizations: null
    }
    this.tinymceOptions = CourseService.getTinymceConfiguration()
    this.workspaces = []
    this.workspace = null
    this.organizations = []
    this.organizationsList = []
    this.initializeCursus()
  }

  initializeCursus() {
    const url = Routing.generate('api_get_workspaces')
    this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        datas.forEach(w => this.workspaces.push(w))
      }
    })

    if (!this.parentId) {
      const organizationsUrl = Routing.generate('claro_cursus_organizations_retrieve')
      this.$http.get(organizationsUrl).then(d => {
        if (d['status'] === 200) {
          const datas = JSON.parse(d['data'])
          datas.forEach(o => {
            this.organizationsList.push(o)
            this.organizations.push(o)
          })
        }
      })
    }
  }

  submit() {
    this.resetErrors()

    if (!this.cursus['title']) {
      this.cursusErrors['title'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.cursusErrors['title'] = null
    }

    if (!this.cursus['code']) {
      this.cursusErrors['code'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.cursusErrors['code'] = null
    }

    if (!this.parentId && this.organizations.length === 0) {
      this.cursusErrors['organizations'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.cursusErrors['organizations'] = null
    }

    if (this.workspace) {
      this.cursus['workspace'] = this.workspace['id']
    } else {
      this.cursus['workspace'] = null
    }

    if (!this.parentId) {
      this.cursus['organizations'] = []
      this.organizations.forEach(o => this.cursus['organizations'].push(o['id']))
    }

    if (this.isValid()) {
      const checkCodeUrl = Routing.generate('api_get_cursus_by_code_without_id', {code: this.cursus['code']})
      this.$http.get(checkCodeUrl).then(d => {
        if (d['status'] === 200) {
          if (d['data'] === 'null') {
            const url = !this.parentId ?
              Routing.generate('api_post_cursus_creation') :
              Routing.generate('api_post_cursus_child_creation', {parent: this.parentId})
            this.FormBuilderService.submit(url, {cursusDatas: this.cursus}).then(d => {
              this.callback(d['data'])
              this.$uibModalInstance.close()
            })
          } else {
            this.cursusErrors['code'] = Translator.trans('form_not_unique_error', {}, 'cursus')
          }
        }
      })
    }
  }

  resetErrors() {
    for (const key in this.cursusErrors) {
      this.cursusErrors[key] = null
    }
  }

  isValid() {
    let valid = true

    for (const key in this.cursusErrors) {
      if (this.cursusErrors[key]) {
        valid = false
        break
      }
    }

    return valid
  }
}
