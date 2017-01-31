/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

import NotBlank from '#/main/core/form/Validator/NotBlank'

export default class CreateNoteCtrl {
  constructor(service, $location) {
    this.deck = service.getDeck()
    this.$location = $location
    this.deckNode = service.getDeckNode()
    this.canEdit = service._canEdit
    this.noteTypes = []
    this.idNoteTypeChoosen = 0
    this.noteTypeChoosen = null
    this.noteTypeField = [
      'type',
      'select',
      {
        values: [],
        label: 'note_type',
        choice_name: 'name',
        choice_value: 'id',
        validators: [new NotBlank()]
      }
    ]
    this.fieldValues = []
    this.newCards = []
    this.fieldTypes = ['text', 'image']

    this.errorMessage = null
    this.errors = []
    this._service = service

    service.findAllNoteType().then(d => {
      this.noteTypes = d.data
      this.noteTypeField[2].values = d.data
    })
  }

  createNote(form) {
    if (form.$valid) {
      const fields = []
      let fieldLabel = null

      for (let i = 0; i < this.fieldValues.length; i++) {
        fieldLabel = this.noteTypeChoosen.field_labels[i]
        fields[i] = {
          'id': fieldLabel.id,
          'fieldValue': this.fieldValues[i]
        }
      }

      this._service.createNote(this.noteTypeChoosen, fields).then(
        d => {
          this.deck.notes.push(d.data)
          this.newCards = this._service.findNewCardToLearn(this.deck)
          this.$location.path('/')
        },
        d => {
          this.errorMessage = 'errors.note.creation_failure'
          this.errors = d.data
        }
      )
      this._resetForm(form)
    }
  }

  updateNoteTypeChoosen() {
    if (this.idNoteTypeChoosen != 0) {
      this.noteTypes.forEach(
        (element) => {
          if (this.idNoteTypeChoosen == element.id) {
            this.noteTypeChoosen = element
          }
        }
      )
    }
  }

  _resetForm(form) {
    this.errorMessage = null
    this.errors = []
    this.fieldValues = []
    form.$setPristine()
    form.$setUntouched()
  }
}
