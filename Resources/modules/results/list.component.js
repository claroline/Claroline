import createTemplate from './add.partial.html'
import editTemplate from './edit.partial.html'

export default class ListComponent {
  constructor (results, modal) {
    this.results = results.getResults()
    this.users = results.getUsers()
    this.editedResult = null
    this.selectedUser = null
    this.selectedUserMark = null
    this._modal = modal
    this._modalInstance = null
  }

  onCreate () {
    this._modalInstance = this._modal.open(createTemplate)

    this._modalInstance.result.then(
      () => console.log('res'),
      () => console.log('closed')
    )
  }

  onEdit (result) {
    this.editedResult = result
    this._modalInstance = this._modal.open(editTemplate)

    this._modalInstance.result.then(
      () => console.log('res', this.editedResult),
      () => console.log('closed')
    )
  }

  onRemove (result) {
    this.results.splice(this.results.indexOf(result), 1)
  }

  onSubmitEdit () {
    this._modalInstance.close()
  }

  onSubmitNew (form) {
    if (form.$valid) {
      console.log(this.selectedUser, this.selectedUserMark)
      this._resetForm(form)
      this._modalInstance.close()
    }
  }

  onCancel (form) {
    this._resetForm(form)
    this._modalInstance.dismiss()
  }

  _resetForm(form) {
    this.editedResult = null
    this.selectedUser = null
    this.selectedUserMark = null
    form.$setPristine()
    form.$setUntouched()
  }
}
