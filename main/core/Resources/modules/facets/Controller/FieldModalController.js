export default class ModalController {
  constructor (form, title, submit, model, $uibModalInstance, $http, FormBuilderService, ClarolineAPIService, dragulaService, $scope) {
    this.form = form
    this.title = title
    this.submit = submit
    this.model = this.field = model
    this.$uibModalInstance = $uibModalInstance
    this.FormBuilderService = FormBuilderService
    this.ClarolineAPIService = ClarolineAPIService
    this.dragulaService = dragulaService
    this.newChoice = {}
    this.$http = $http
    this.idChoice = 1

    dragulaService.options($scope, 'field-bag', {
      moves: function (el, container, handle) {
        return handle.className === 'handle'
      }
    })
  }

  onSubmit (form) {
    if (form.$valid) this.$uibModalInstance.close(this.model)
  }

  addChoice () {
    // if the fiels already exists
    if (this.model.id) {
      this.FormBuilderService.submit(Routing.generate('api_post_facet_field_choice', {field: this.field.id}), {'choice': this.newChoice}).then(
        d => {
          this.model.field_facet_choices.push(d.data)
        },
        d => {
          ClarolineAPIService.errorModal()
        }
      )
    } else {
      if (!this.model.field_facet_choices) this.model.field_facet_choices = []
      this.model.field_facet_choices.push({label: this.newChoice.label, id: this.idChoice})
      this.idChoice++
    }

    this.newChoice = {}
  }

  removeChoice (choice) {
    if (this.model.id) {
      this.$http.delete(
        Routing.generate('api_delete_facet_field_choice', {choice: choice.id})
      ).then(
        d => {
          this.ClarolineAPIService.removeElements([choice], this.model.field_facet_choices)
        },
        d => {
          ClarolineAPIService.errorModal()
        }
      )
    } else {
      this.ClarolineAPIService.removeElements([choice], this.model.field_facet_choices)
    }
  }
}
