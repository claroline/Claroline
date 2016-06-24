import NotBlank from '../../form/Validator/NotBlank'

export default class ProfileController {
  constructor ($http, $scope, FormBuilderService) {
    this.$http = $http
    this.$scope = $scope
    this.FormBuilderService = FormBuilderService
    this.user = []
    this.arLinks = []
    this.facets = []
    this.userId = window['userId']
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

    this.fieldTypes = ['text', 'number', 'date', 'radio', 'select', 'checkboxes', 'country', 'email']
  }

  onSubmit (facet) {
    const fields = []

    facet.panels.forEach(panel => {
      panel.fields.forEach(field => {
        fields.push(field)
      })
    })

    var data = this.FormBuilderService.submit(Routing.generate('api_put_profile_fields', {user: this.userId}), {'fields': fields}, 'PUT').then(
      d => {
      },
      d => ClarolineAPIService.errorModal()
    )
  }

  switchProfileMode () {
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

  getFieldDefinition (field) {
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
}
