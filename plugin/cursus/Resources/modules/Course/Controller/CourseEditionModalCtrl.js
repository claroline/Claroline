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

export default class CourseEditionModalCtrl {
  constructor($rootScope, $http, $uibModalInstance, FormBuilderService, CourseService, title, course, callback) {
    this.$rootScope = $rootScope
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.FormBuilderService = FormBuilderService
    this.title = title
    this.source = course
    this.callback = callback
    this.course = {
      title: null,
      code: null,
      description: '',
      icon: null,
      publicRegistration: false,
      publicUnregistration: false,
      defaultSessionDuration: 1,
      withSessionEvent: true,
      workspace: null,
      workspaceModel: null,
      maxUsers: '',
      tutorRoleName: '',
      learnerRoleName: '',
      userValidation: false,
      organizationValidation: false,
      registrationValidation: false,
      validators: [],
      displayOrder: 500,
      organizations: []
    }
    this.courseErrors = {
      title: null,
      code: null,
      defaultSessionDuration: null,
      maxUsers: null,
      displayOrder: null,
      organizations: null
    }
    this.tinymceOptions = CourseService.getTinymceConfiguration()
    this.cursusList = []
    this.cursus = []
    this.validatorsRoles = []
    this.validators = []
    this.workspaces = []
    this.workspace = null
    this.workspaceModels = []
    this.model = null
    this.rolesChoices = []
    this.organizations = []
    this.lockedOrganizations = []
    this.organizationsList = []
    this._userpickerCallback = this._userpickerCallback.bind(this)
    this.initializeCourse()
  }

  _userpickerCallback(datas) {
    this.validators = datas === null ? [] : datas
    this.refreshScope()
  }

  initializeCourse() {
    if (this.source['tutorRoleName']) {
      this.course['tutorRoleName'] = this.source['tutorRoleName']
    }
    if (this.source['learnerRoleName']) {
      this.course['learnerRoleName'] = this.source['learnerRoleName']
    }
    if (this.source['icon']) {
      this.course['icon'] = this.source['icon']
    }
    const workspacesUrl = Routing.generate('api_get_workspaces')
    this.$http.get(workspacesUrl).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        datas.forEach(w => this.workspaces.push(w))

        if (this.source['workspace']) {
          const selectedWorkspace = this.workspaces.find(w => w['id'] === this.source['workspace']['id'])
          this.workspace = selectedWorkspace
          this.manageRolesChoices()
        }
      }
    })
    const workspaceModelsUrl = Routing.generate('api_get_workspace_models')
    this.$http.get(workspaceModelsUrl).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        datas.forEach(wm => this.workspaceModels.push(wm))

        if (this.source['workspaceModel']) {
          const selectedModel = this.workspaceModels.find(wm => wm['id'] === this.source['workspaceModel']['id'])
          this.model = selectedModel
          this.manageRolesChoices()
        }
      }
    })
    const validatorsRolesUrl = Routing.generate('api_get_validators_roles')
    this.$http.get(validatorsRolesUrl).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        datas.forEach(r => this.validatorsRoles.push(r['id']))
      }
    })
    const organizationsUrl = Routing.generate('claro_cursus_organizations_retrieve')
    this.$http.get(organizationsUrl).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        datas.forEach(o => this.organizationsList.push(o))

        if (this.source['organizations']) {
          this.source['organizations'].forEach(o => {
            const selectedOrganization = this.organizationsList.find(organization => organization['id'] === o['id'])

            if (selectedOrganization) {
              this.organizations.push(selectedOrganization)
            } else {
              o['disabled'] = true
              this.organizationsList.push(o)
              this.lockedOrganizations.push(o)
            }
          })
        }
      }
    })
    this.source['validators'].forEach(v => this.validators.push(v))
    this.course['title'] = this.source['title']
    this.course['code'] = this.source['code']
    this.course['publicRegistration'] = this.source['publicRegistration']
    this.course['publicUnregistration'] = this.source['publicUnregistration']
    this.course['defaultSessionDuration'] = this.source['defaultSessionDuration']
    this.course['displayOrder'] = this.source['displayOrder']
    this.course['withSessionEvent'] = this.source['withSessionEvent']
    this.course['userValidation'] = this.source['userValidation']
    this.course['organizationValidation'] = this.source['organizationValidation']
    this.course['registrationValidation'] = this.source['registrationValidation']

    if (this.source['description']) {
      this.course['description'] = this.source['description']
    }
    if (this.source['maxUsers']) {
      this.course['maxUsers'] = this.source['maxUsers']
    }
  }

  displayValidators() {
    let value = ''
    this.validators.forEach(u => value += `${u['firstName']} ${u['lastName']}, `)

    return value
  }

  submit() {
    this.resetErrors()

    if (!this.course['title']) {
      this.courseErrors['title'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.courseErrors['title'] = null
    }

    if (!this.course['code']) {
      this.courseErrors['code'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.courseErrors['code'] = null
    }

    if (this.course['defaultSessionDuration'] === null || this.course['defaultSessionDuration'] === undefined) {
      this.courseErrors['defaultSessionDuration'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.course['defaultSessionDuration'] = parseInt(this.course['defaultSessionDuration'])

      if (this.course['defaultSessionDuration'] < 0) {
        this.courseErrors['defaultSessionDuration'] = Translator.trans('form_number_superior_error', {value: 0}, 'cursus')
      }
    }

    if (this.course['displayOrder'] === null || this.course['displayOrder'] === undefined) {
      this.courseErrors['displayOrder'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.course['displayOrder'] = parseInt(this.course['displayOrder'])
      this.courseErrors['displayOrder'] = null
    }

    if (this.course['maxUsers']) {
      this.course['maxUsers'] = parseInt(this.course['maxUsers'])

      if (this.course['maxUsers'] < 0) {
        this.courseErrors['maxUsers'] = Translator.trans('form_number_superior_error', {value: 0}, 'cursus')
      }
    }

    if (this.organizations.length === 0) {
      this.courseErrors['organizations'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.courseErrors['organizations'] = null
    }

    if (this.workspace) {
      this.course['workspace'] = this.workspace['id']
    } else {
      this.course['workspace'] = null
    }

    if (this.model) {
      this.course['workspaceModel'] = this.model['id']
    } else {
      this.course['workspaceModel'] = null
    }

    if (this.course['tutorRoleName'] === null) {
      this.course['tutorRoleName'] = ''
    }

    if (this.course['learnerRoleName'] === null) {
      this.course['learnerRoleName'] = ''
    }
    this.course['validators'] = []
    this.validators.forEach(v => this.course['validators'].push(v['id']))
    this.course['organizations'] = []
    this.lockedOrganizations.forEach(o => this.course['organizations'].push(o['id']))
    this.organizations.forEach(o => this.course['organizations'].push(o['id']))

    if (this.isValid()) {
      const checkCodeUrl = Routing.generate('api_get_course_by_code_without_id', {code: this.course['code'], id: this.source['id']})
      this.$http.get(checkCodeUrl).then(d => {
        if (d['status'] === 200) {
          if (d['data'] === 'null') {
            const url = Routing.generate('api_put_course_edition', {course: this.source['id']})
            this.FormBuilderService.submit(url, {courseDatas: this.course}).then(d => {
              this.callback(d['data'])
              this.$uibModalInstance.close()
            })
          } else {
            this.courseErrors['code'] = Translator.trans('form_not_unique_error', {}, 'cursus')
          }
        }
      })
    }
  }

  resetErrors() {
    for (const key in this.courseErrors) {
      this.courseErrors[key] = null
    }
  }

  isValid() {
    let valid = true

    for (const key in this.courseErrors) {
      if (this.courseErrors[key]) {
        valid = false
        break
      }
    }

    return valid
  }

  isUserpickerAvailable() {
    return this.validatorsRoles.length > 0
  }

  getSelectedUsersIds() {
    let selectedUsersIds = []
    this.validators.forEach(v => {
      selectedUsersIds.push(v['id'])
    })

    return selectedUsersIds
  }

  openUserPicker() {
    let userPicker = new UserPicker()
    const options = {
      picker_name: 'validators-picker',
      picker_title: Translator.trans('validators_selection', {}, 'cursus'),
      multiple: true,
      selected_users: this.getSelectedUsersIds(),
      forced_roles: this.validatorsRoles,
      return_datas: true,
      filter_admin_orgas: true
    }
    userPicker.configure(options, this._userpickerCallback)
    userPicker.open()
  }

  manageRolesChoices() {
    if (this.workspace) {
      this.getWorkspaceRoles()
    } else if (this.model) {
      this.getModelRoles()
    } else {
      this.rolesChoices = []
    }
  }

  getWorkspaceRoles() {
    if (this.workspace) {
      const url = Routing.generate('course_workspace_roles_translation_keys_retrieve', {workspace: this.workspace['id']})
      this.$http.get(url).then(d => {
        if (d['status'] === 200) {
          this.rolesChoices = []
          d['data'].forEach(r => this.rolesChoices.push(r))

          if (this.rolesChoices.indexOf(this.course['tutorRoleName']) === -1) {
            this.course['tutorRoleName'] = null
          }

          if (this.rolesChoices.indexOf(this.course['learnerRoleName']) === -1) {
            this.course['learnerRoleName'] = null
          }
        }
      })
    }
  }

  getModelRoles() {
    if (this.model) {
      const url = Routing.generate('ws_model_roles_translation_keys_retrieve', {model: this.model['id']})
      this.$http.get(url).then(d => {
        if (d['status'] === 200) {
          this.rolesChoices = []
          d['data'].forEach(r => this.rolesChoices.push(r))

          if (this.rolesChoices.indexOf(this.course['tutorRoleName']) === -1) {
            this.course['tutorRoleName'] = null
          }

          if (this.rolesChoices.indexOf(this.course['learnerRoleName']) === -1) {
            this.course['learnerRoleName'] = null
          }
        }
      })
    }
  }

  refreshScope() {
    this.$rootScope.$apply()
  }
}
