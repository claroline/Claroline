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

export default class SessionCreationModalCtrl {
  constructor($rootScope, $http, $uibModalInstance, CursusService, CourseService, title, course, callback) {
    this.$rootScope = $rootScope
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.CursusService = CursusService
    this.CourseService = CourseService
    this.title = title
    this.course = course
    this.callback = callback
    this.session = {
      name: null,
      startDate: null,
      endDate: null,
      description: null,
      defaultSession: false,
      publicRegistration: false,
      publicUnregistration: false,
      cursus: [],
      maxUsers: null,
      userValidation: false,
      organizationValidation: false,
      registrationValidation: false,
      validators: [],
      eventRegistrationType: 0,
      displayOrder: 500,
      color: null
    }
    this.sessionErrors = {
      name: null,
      startDate: null,
      endDate: null,
      maxUsers: null,
      displayOrder: null
    }
    this.dateOptions = {
      formatYear: 'yy',
      startingDay: 1,
      placeHolder: 'jj/mm/aaaa'
    }
    this.dates = {
      start: {format: 'dd/MM/yyyy', open: false},
      end: {format: 'dd/MM/yyyy', open: false}
    }
    this.tinymceOptions = CourseService.getTinymceConfiguration()
    this.cursusList = []
    this.cursus = []
    this.validatorsRoles = []
    this.validators = []
    this.eventRegistrationTypeChoices = [
      {value: 0, name: Translator.trans('event_registration_automatic', {}, 'cursus')},
      {value: 1, name: Translator.trans('event_registration_manual', {}, 'cursus')},
      {value: 2, name: Translator.trans('event_registration_public', {}, 'cursus')}
    ]
    this.eventRegistrationType = this.eventRegistrationTypeChoices[0]
    this._userpickerCallback = this._userpickerCallback.bind(this)
    this.initializeSession()
  }

  _userpickerCallback(datas) {
    this.validators = datas === null ? [] : datas
    this.refreshScope()
  }

  initializeSession() {
    this.CursusService.getRootCursus().then(d => {
      d.forEach(c => this.cursusList.push(c))
    })
    const url = Routing.generate('api_get_validators_roles')
    this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        const datas = JSON.parse(d['data'])
        datas.forEach(r => this.validatorsRoles.push(r['id']))
      }
    })
    const startDate = new Date()
    let endDate = new Date(startDate)
    endDate.setDate(endDate.getDate() + this.course['defaultSessionDuration'])
    this.session['startDate'] = startDate
    this.session['endDate'] = endDate
    this.session['publicRegistration'] = this.course['publicRegistration']
    this.session['publicUnregistration'] = this.course['publicUnregistration']
    this.session['userValidation'] = this.course['userValidation']
    this.session['organizationValidation'] = this.course['organizationValidation']
    this.session['registrationValidation'] = this.course['registrationValidation']
    this.session['displayOrder'] = this.course['displayOrder']

    if (this.course['description']) {
      this.session['description'] = this.course['description']
    }
  }

  displayValidators() {
    let value = ''
    this.validators.forEach(u => value += `${u['firstName']} ${u['lastName']}, `)

    return value
  }

  submit() {
    this.resetErrors()

    if (!this.session['name']) {
      this.sessionErrors['name'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.sessionErrors['name'] = null
    }

    if (!this.session['startDate']) {
      if (this.session['startDate'] === null) {
        this.sessionErrors['startDate'] = Translator.trans('form_not_blank_error', {}, 'cursus')
      } else {
        this.sessionErrors['startDate'] = Translator.trans('form_not_valid_error', {}, 'cursus')
      }
    } else {
      this.sessionErrors['startDate'] = null
    }

    if (!this.session['endDate']) {
      if (this.session['endDate'] === null) {
        this.sessionErrors['endDate'] = Translator.trans('form_not_blank_error', {}, 'cursus')
      } else {
        this.sessionErrors['endDate'] = Translator.trans('form_not_valid_error', {}, 'cursus')
      }
    } else {
      this.sessionErrors['endDate'] = null
    }

    if (this.session['displayOrder'] === null || this.session['displayOrder'] === undefined) {
      this.sessionErrors['displayOrder'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.session['displayOrder'] = parseInt(this.session['displayOrder'])
      this.sessionErrors['displayOrder'] = null
    }

    if (this.session['maxUsers']) {
      this.session['maxUsers'] = parseInt(this.session['maxUsers'])

      if (this.session['maxUsers'] < 0) {
        this.sessionErrors['maxUsers'] = Translator.trans('form_number_superior_error', {value: 0}, 'cursus')
      }
    }

    if (this.eventRegistrationType) {
      this.session['eventRegistrationType'] = this.eventRegistrationType['value']
    } else {
      this.session['eventRegistrationType'] = 0
    }
    this.session['cursus'] = []
    this.cursus.forEach(c => {
      this.session['cursus'].push(c['id'])
    })
    this.session['validators'] = []
    this.validators.forEach(v => {
      this.session['validators'].push(v['id'])
    })

    if (this.isValid()) {
      const url = Routing.generate('api_post_session_creation', {course: this.course['id']})
      this.$http.post(url, {sessionDatas: this.session}).then(d => {
        this.callback(d['data'])
        this.$uibModalInstance.close()
      })
    }
  }

  resetErrors() {
    for (const key in this.sessionErrors) {
      this.sessionErrors[key] = null
    }
  }

  isValid() {
    let valid = true

    for (const key in this.sessionErrors) {
      if (this.sessionErrors[key]) {
        valid = false
        break
      }
    }

    return valid
  }

  isUserpickerAvailable() {
    return this.validatorsRoles.length > 0
  }

  openDatePicker(type) {
    if (type === 'start') {
      this.dates['start']['open'] = true
    } else if (type === 'end') {
      this.dates['end']['open'] = true
    }
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

  refreshScope() {
    this.$rootScope.$apply()
  }
}
