export default class ModalController {
  constructor (form, title, submit, model, $uibModalInstance) {
    this.form = form
    this.title = title
    this.submit = submit
    this.model = model
    this.$uibModalInstance = $uibModalInstance
  }

  onSubmit (form) {
    if (form.$valid) this.$uibModalInstance.close(this.model)
  }
}
