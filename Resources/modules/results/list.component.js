import confirmTemplate from './confirm.partial.html'
import createTemplate from './create.partial.html'
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
    this._modalFactory = modal
    this._modalInstance = null
  }

  displayCreationForm () {
    this._modal(createTemplate)
  }

  createResult (form) {
    if (form.$valid) {
      this._service.createMark(this.createdMark, () => {
        this._modal(errorTemplate, 'CREATION FAILED')
      })
      this._resetForm(form)
      this._closeModal()
    }
  }

  displayEditionForm (result) {
    this.editedMark.original = result
    this.editedMark.newValue = result.mark
    this._modal(editTemplate)
  }

  editResult (form) {
    if (form.$valid) {
      this._closeModal()
      this._service.editMark(
        this.editedMark.original,
        this.editedMark.newValue,
        () => this._modal(errorTemplate, 'EDITION FAILED')
      )
    }
  }

  confirmDeletion (result) {
    this._deletedResult = result
    this._modal(confirmTemplate)
  }

  deleteResult () {
    this._service.deleteMark(
      this._deletedResult,
      () => this._modal(errorTemplate, 'SUPPRESSION FAILED')
    )
    this._closeModal()
  }

  cancel (form) {
    if (form) {
      this._resetForm(form)
    }

    this._modalInstance.dismiss()
  }

  _modal (template, errorMessage) {
    if (errorMessage) {
      this.errorMessage = errorMessage
    }

    this._modalInstance = this._modalFactory.open(template)
  }

  _closeModal () {
    this._modalInstance.close()
  }

  _resetForm (form) {
    this.createdMark = {}
    this.editedMark = {}
    this._deletedResult = null
    form.$setPristine()
    form.$setUntouched()
  }
}
