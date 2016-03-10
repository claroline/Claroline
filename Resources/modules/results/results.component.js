import form from './result-form.component.html'

export default class ResultsComponent {
  constructor (results, modal) {
    this.modal = modal
    this.modalInstance = null
    this.editedResult = null
    this.results = results.fetch()
  }

  onEdit(result) {
    this.editedResult = result
    this.modalInstance = this.modal.open()

    this.modalInstance.result.then(
      () => console.log('res', this.editedResult),
      () => console.log('closed')
    )
  }

  onRemove(result) {
    this.results.splice(this.results.indexOf(result), 1)
  }

  onSubmitResult(result) {
    this.modalInstance.close()
  }

  onCancel() {
    this.modalInstance.dismiss()
  }
}
