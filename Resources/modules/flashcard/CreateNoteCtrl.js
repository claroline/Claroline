/*
 * This file is part of the Claroline Connect package.
 * 
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

export default class CreateNoteCtrl {
  constructor (service, $http) {
    this.deck = service.getDeck()
    this.deckNode = service.getDeckNode()
    this.noteTypes = []
    this.noteTypeChoosenId = 0
    this.noteTypeChoosen = null
    this.fieldValues = []
    this.newCards = []

    this.errorMessage = null
    this.errors = []
    this._service = service

    service.findAllNoteType().then(d => this.noteTypes = d.data)
  }

  createNote (form) {
    if (form.$valid) {
      const fields = []
      let fieldLabel = null

      for(let i=0; i<this.fieldValues.length; i++) {
        fieldLabel = this.noteTypeChoosen.field_labels[i]
        fields[i] = {
          "id": fieldLabel.id,
          "value": this.fieldValues[i]
        }
      }

      this._service.createNote(this.noteTypeChoosen, fields).then(
        d => { 
          this.deck.notes.push(d.data)
          this.newCards = this._service.findNewCardToLearn(this.deck)
        },
        d => {
          // Must do something to delete the created note in this controller
          // but for the moment the created note is not added to the
          // attributes.
          // ...
          this.errorMessage('errors.note.creation_failure')
          this.errors = d.data
        }
      )
      this._resetForm(form)
    }
  }

  _resetForm (form) {
    this.errorMessage = null
    this.errors = []
    this.fieldValues = []
    form.$setPristine()
    form.$setUntouched()
  }
}
