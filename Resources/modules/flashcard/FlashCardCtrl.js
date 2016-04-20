/*
 * This file is part of the Claroline Connect package.
 * 
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

import createNoteTemplate from './createNote.partial.html'
import errorTemplate from './error.partial.html'

export default class FlashCardCtrl {
  constructor (service, modal, $http) {
    this.deck = service.getDeck()
    this.deckNode = service.getDeckNode()
    this.newCards = []
    this.learingCards = []

    this.editedMark = {}
    this.createdMark = {}
    this.importFile = null
    this.errorMessage = null
    this.errors = []
    this._deletedResult = null
    this._service = service
    this._modalFactory = modal
    this._modalInstance = null

    service.findNewCardToLearn(this.deck).then(d => this.newCards = d.data)
    service.findCardToLearn(this.deck).then(d => this.learningCards = d.data)
  }

  displayNoteCreationForm () {
    this._modal(createNoteTemplate)
  }

  createResult (form) {
    if (form.$valid) {
      this._service.createMark(this.createdMark, () => {
        this._modal(errorTemplate, 'errors.mark.creation_failure')
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
        () => this._modal(errorTemplate, 'errors.mark_edition_failure')
      )
    }
  }

  displayImportForm () {
    this._modal(importTemplate)
  }

  importResults (form) {
    this._service.importMarks(this.importFile, errors =>
      this._modal(errorTemplate, 'errors.mark_import_failure', errors)
    )
    this._resetForm(form)
    this._closeModal()
  }

  confirmDeletion (result) {
    this._deletedResult = result
    this._modal(confirmTemplate)
  }

  deleteResult () {
    this._service.deleteMark(
      this._deletedResult,
      () => this._modal(errorTemplate, 'errors.mark_suppression_failure')
    )
    this._closeModal()
  }

  cancel (form) {
    if (form) {
      this._resetForm(form)
    }

    this._modalInstance.dismiss()
  }

  _modal (template, errorMessage, errors) {
    if (errorMessage) {
      this.errorMessage = errorMessage
    }

    if (errors) {
      this.errors = errors
    }

    this._modalInstance = this._modalFactory.open(template)
  }

  _closeModal () {
    this._modalInstance.close()
  }

  _resetForm (form) {
    this.createdMark = {}
    this.editedMark = {}
    this.importFile = null
    this.errorMessage = null
    this.errors = []
    this._deletedResult = null
    form.$setPristine()
    form.$setUntouched()
  }
}
