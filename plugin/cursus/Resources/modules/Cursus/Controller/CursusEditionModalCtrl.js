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

export default class CursusEditionModalCtrl {
  constructor($http, $uibModalInstance, FormBuilderService, CourseService, title, cursus, callback) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.FormBuilderService = FormBuilderService
    this.callback = callback
    this.title = title
    this.source = cursus
    this.cursusId = cursus['id']
    this.cursus = {
      title: null,
      code: null,
      description: '',
      icon: '',
      workspace: null,
      blocking: false,
      color: ''
    }
    this.cursusErrors = {
      title: null,
      code: null
    }
    this.tinymceOptions = CourseService.getTinymceConfiguration()
    this.workspaces = []
    this.workspace = null
    this.initializeCursus()
  }

  initializeCursus () {
    this.cursus['title'] = this.source['title']
    this.cursus['blocking'] = this.source['blocking']

    if (this.source['code']) {
      this.cursus['code'] = this.source['code']
    }

    if (this.source['description']) {
      this.cursus['description'] = this.source['description']
    }

    if (this.source['details']['color']) {
      this.cursus['color'] = this.source['details']['color']
    }
    const workspacesUrl = Routing.generate('api_get_workspaces')
    this.$http.get(workspacesUrl).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        datas.forEach(w => this.workspaces.push(w))

        if (this.source['workspace']) {
          const selectedWorkspace = this.workspaces.find(w => w['id'] === this.source['workspace']['id'])
          this.workspace = selectedWorkspace
        }
      }
    })
  }

  submit () {
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

    if (this.workspace) {
      this.cursus['workspace'] = this.workspace['id']
    } else {
      this.cursus['workspace'] = null
    }

    if (this.isValid()) {
      const checkCodeUrl = Routing.generate('api_get_cursus_by_code_without_id', {code: this.cursus['code'], id : this.source['id']})
      this.$http.get(checkCodeUrl).then(d => {
        if (d['status'] === 200) {
          if (d['data'] === 'null') {
            const url = Routing.generate('api_put_cursus_edition', {cursus: this.source['id']})
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

  resetErrors () {
    for (const key in this.cursusErrors) {
      this.cursusErrors[key] = null
    }
  }

  isValid () {
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
