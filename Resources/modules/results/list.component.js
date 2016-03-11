import confirmTemplate from './confirm.partial.html'
import createTemplate from './add.partial.html'
import editTemplate from './edit.partial.html'
import errorTemplate from './error.partial.html'

export default class ListComponent {
  constructor (service, modal) {
    this.results = service.getResults()
    this.users = service.getUsers()
    this.editedResult = null
    this.editedMark = null
    this.selectedUserName = null
    this.selectedUserMark = null
    this.errorMessage = null
    this._deletedResult = null
    this._service = service
    this._modal = modal
    this._modalInstance = null
  }

  onCreate () {
    this._modalInstance = this._modal.open(createTemplate)
  }

  onEdit (result) {
    this.editedResult = result
    this.editedMark = result.mark
    this._modalInstance = this._modal.open(editTemplate)
  }

  onDelete (result) {
    this._deletedResult = result
    this._modalInstance = this._modal.open(confirmTemplate)
  }

  onSubmitDelete () {
    this._deleteResult(this._deletedResult)
    this._service
      .deleteMark(this._deletedResult.markId)
      .then(null, () => this._rollbackSuppression(this._deletedResult))
    this._modalInstance.close()
  }

  onSubmitEdit (form) {
    if (form.$valid) {
      const originalMark = this.editedResult.mark
      this.editedResult.mark = this.editedMark

      this._service
        .editMark(this.editedResult.markId, this.editedMark)
        .then(null, () => this.editedResult.mark = originalMark)

      this._modalInstance.close()
    }
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
    this.deletedResult = null
    this.editedResult = null
    this.editedMark = null
    this.selectedUserName = null
    this.selectedUserMark = null
    form.$setPristine()
    form.$setUntouched()
  }

  _deleteResult (result) {
    this.results.splice(this.results.indexOf(result), 1)
  }

  _rollbackCreation(result) {
    this.errorMessage = 'CREATION FAILED'
    this._modalInstance = this._modal.open(errorTemplate)
    this._deleteResult(result)
  }

  _rollbackSuppression(result) {
    this.errorMessage = 'SUPPRESSION FAILED'
    this._modalInstance = this._modal.open(errorTemplate)
    this.results.push(result)
  }
}
