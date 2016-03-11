import createTemplate from './add.partial.html'
import editTemplate from './edit.partial.html'
import errorTemplate from './error.partial.html'

export default class ListComponent {
  constructor (service, modal) {
    this.results = service.getResults()
    this.users = service.getUsers()
    this.editedResult = null
    this.selectedUserName = null
    this.selectedUserMark = null
    this.errorMessage = null
    this._service = service
    this._modal = modal
    this._modalInstance = null
  }

  onCreate () {
    this._modalInstance = this._modal.open(createTemplate)
  }

  onEdit (result) {
    this.editedResult = result
    this._modalInstance = this._modal.open(editTemplate)
  }

  onRemove (result) {
    this._removeResult(result)
    this._service
      .deleteMark(result.markId)
      .then(null, () => this._rollbackSuppression(result))
  }

  onSubmitEdit () {
    this._modalInstance.close()
  }

  onSubmitNew (form) {
    if (form.$valid) {
      const user = this.users.find(user => user.name === this.selectedUserName)
      const result = { name: user.name, mark: this.selectedUserMark }
      this.results.push(result);

      this._service
        .createMark(user.id, this.selectedUserMark)
        .then(
          response => result.markId = response.data,
          () => this._rollbackCreation(result)
        )

      this._resetForm(form)
      this._modalInstance.close()
    }
  }

  onCancel (form) {
    if (form) {
      this._resetForm(form)
    }

    this._modalInstance.dismiss()
  }

  _resetForm (form) {
    this.editedResult = null
    this.selectedUserName = null
    this.selectedUserMark = null
    form.$setPristine()
    form.$setUntouched()
  }

  _removeResult (result) {
    this.results.splice(this.results.indexOf(result), 1)
  }

  _rollbackCreation(result) {
    this.errorMessage = 'CREATION FAILED'
    this._modalInstance = this._modal.open(errorTemplate)
    this._removeResult(result)
  }

  _rollbackSuppression(result) {
    this.errorMessage = 'SUPPRESSION FAILED'
    this._modalInstance = this._modal.open(errorTemplate)
    this.results.push(result)
  }
}
