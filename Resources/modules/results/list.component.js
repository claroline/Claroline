import confirmTemplate from './confirm.partial.html'
import createTemplate from './add.partial.html'
import editTemplate from './edit.partial.html'
import errorTemplate from './error.partial.html'

export default class ListComponent {
  constructor (service, modal) {
    this.results = service.getResults()
    this.users = service.getUsers()
    this.editedMark = {}
    this.createdMark = {}
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
    this.editedMark.original = result
    this.editedMark.newValue = result.mark
    this._modalInstance = this._modal.open(editTemplate)
  }

  onDelete (result) {
    this._deletedResult = result
    this._modalInstance = this._modal.open(confirmTemplate)
  }

  onSubmitDelete () {
    this._service.deleteMark(this._deletedResult, () => {
      this.errorMessage = 'SUPPRESSION FAILED'
      this._modalInstance = this._modal.open(errorTemplate)
    })
    this._modalInstance.close()
  }

  onSubmitEdit (form) {
    if (form.$valid) {
      this._service.editMark(
        this.editedMark.original,
        this.editedMark.newValue,
        () => {
          this.errorMessage = 'EDITION FAILED'
          this._modalInstance = this._modal.open(errorTemplate)
        }
      )
      this._modalInstance.close()
    }
  }

  onSubmitNew (form) {
    if (form.$valid) {
      this._service.createMark(this.createdMark, () => {
        this.errorMessage = 'CREATION FAILED'
        this._modalInstance = this._modal.open(errorTemplate)
      })
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
    this.createdMark = {}
    this.editedMark = {}
    this._deletedResult = null
    form.$setPristine()
    form.$setUntouched()
  }
}
