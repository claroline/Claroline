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

export default class CourseCreationModalCtrl {
  constructor($rootScope, $http, $uibModalInstance, FormBuilderService, CourseService, title, cursusId, callback) {
    this.$rootScope = $rootScope
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.FormBuilderService = FormBuilderService
    this.CourseService = CourseService
    this.title = title
    this.cursusId = cursusId
    this.callback = callback
    this.course = {
      title: null,
      code: null,
      description: '',
      icon: null,
      publicRegistration: false,
      publicUnregistration: false,
      defaultSessionDuration: null,
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
    this.validatorsRoles = []
    this.validators = []
    this.workspaces = []
    this.workspace = null
    this.workspaceModels = []
    this.model = null
    this.rolesChoices = []
    this.organizations = []
    this.organizationsList = []
    this._userpickerCallback = this._userpickerCallback.bind(this)
    this.initializeCourse()
  }

  _userpickerCallback(datas) {
    this.validators = datas === null ? [] : datas
    this.refreshScope()
  }

  initializeCourse() {
    const workspacesUrl = Routing.generate('api_get_workspaces')
    this.$http.get(workspacesUrl).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        datas.forEach(w => this.workspaces.push(w))
      }
    })
    const workspaceModelsUrl = Routing.generate('api_get_workspace_models')
    this.$http.get(workspaceModelsUrl).then(d => {
      if (d['status'] === 200) {
        const datas = d['data']
        datas.forEach(wm => this.workspaceModels.push(wm))
      }
    })
    const validatorsRolesUrl = Routing.generate('api_get_validators_roles')
    this.$http.get(validatorsRolesUrl).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        datas.forEach(r => this.validatorsRoles.push(r['id']))
      }
    })

    if (!this.cursusId) {
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
    this.CourseService.getGeneralParameters().then(d => {
      this.course['defaultSessionDuration'] = d['sessionDefaultDuration']
    })
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

    if (!this.cursusId && this.organizations.length === 0) {
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

    if (!this.cursusId) {
      this.course['organizations'] = []
      this.organizations.forEach(o => this.course['organizations'].push(o['id']))
    }

    if (this.isValid()) {
      const checkCodeUrl = Routing.generate('api_get_course_by_code_without_id', {code: this.course['code']})
      this.$http.get(checkCodeUrl).then(d => {
        if (d['status'] === 200) {
          if (d['data'] === 'null') {
            const url = this.cursusId === null ?
              Routing.generate('api_post_course_creation') :
              Routing.generate('api_post_cursus_course_creation', {cursus: this.cursusId})
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
