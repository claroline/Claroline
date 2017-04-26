import NotBlank from '../../form/Validator/NotBlank'
/*global Routing*/
/*global Translator*/

export default class ProfileController {
  constructor($http, $scope, FormBuilderService, ClarolineAPIService, NgTableParams) {
    this.$http = $http
    this.$scope = $scope
    this.FormBuilderService = FormBuilderService
    this.ClarolineAPIService = ClarolineAPIService
    this.user = []
    this.arLinks = []
    this.facets = []
    this.forms = []
    this.userId = window['userId']
    this.canEdit = window['canEdit']
    this.disabled = true
    this.profileModeLabel = Translator.trans('edit_mode', {}, 'platform')
    this.fieldForms = {}
    $http.get(Routing.generate('api_get_public_user', {user: this.userId})).then(d => {
      this.user = d.data
      this.picturePath = 'uploads/pictures/' + d.data.picture
    })
    $http.get(Routing.generate('api_get_profile_links', {user: this.userId})).then(d => this.arLinks = d.data)
    $http.get(Routing.generate('api_get_profile_facets', {user: this.userId})).then(d => {
      this.facets = d.data
      this.fieldForms = this.getFieldsDefinition(this.facets)
    })
    this.displayCourses = false
    this.displayCourseWorkspace = false
    this.coursesLoaded = false
    this.sessions = []
    this.sessionsTableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.sessions}
    )
    $http.get(Routing.generate('claro_user_profile_courses_tab_options')).then(d => {
      if (d['data']['displayCourses']) {
        this.displayCourses = d['data']['displayCourses']
      }
      if (d['data']['displayWorkspace']) {
        this.displayCourseWorkspace = d['data']['displayWorkspace']
      }
    })

    this.fieldTypes = ['text', 'number', 'date', 'radio', 'select', 'checkboxes', 'country', 'email']
  }

  onSubmit(facet) {
    const fields = []

    facet.panels.forEach(panel => {
      panel.fields.forEach(field => {
        fields.push(field)
      })
    })
    this.FormBuilderService.submit(Routing.generate('api_put_profile_fields', {user: this.userId}), {'fields': fields}, 'PUT').then(
      () => {},
      () => this.ClarolineAPIService.errorModal()
    )
  }

  switchProfileMode() {
    if (this.disabled) {
      this.disabled = false
      this.profileModeLabel = Translator.trans('display_mode', {}, 'platform')
    } else {
      this.disabled = true
      this.profileModeLabel = Translator.trans('edit_mode', {}, 'platform')
    }

    this.fieldForms = this.getFieldsDefinition(this.facets)
  }

  getFieldsDefinition(facets) {
    const fieldForms = {}
    facets.forEach(facet => {
      facet.panels.forEach(panel => {
        panel.fields.forEach(field => {
          fieldForms[field.id] = this.getFieldDefinition(field)
        })
      })
    })

    return fieldForms
  }

  getFieldDefinition(field) {
    const validators = []

    if (field.is_required) {
      validators.push(new NotBlank())
    }

    return [
      field.name,
      this.fieldTypes[field.type - 1],
      {
        'values': field.field_facet_choices,
        'disabled': !(!this.disabled && field.is_editable),
        'validators': validators,
        'show_errors': !this.disabled,
        'choice_value': 'value'
      }
    ]
  }

  loadCoursesProfile() {
    if (this.displayCourses && !this.coursesLoaded) {
      this.coursesLoaded = true
      const url = Routing.generate('claro_user_profile_closed_sessions', {user: this.userId})
      this.$http.get(url).then(d => {
        if (d['status'] === 200) {
          this.sessions.splice(0, this.sessions.length)
          const data = JSON.parse(d['data'])
          data.forEach(s => {
            s['courseId'] = s['course']['id']
            s['courseTitle'] = s['course']['title']
            s['courseCode'] = s['course']['code']
            s['courseDescription'] = s['course']['description']
            this.sessions.push(s)
          })
        }
      })
    }
  }
}
