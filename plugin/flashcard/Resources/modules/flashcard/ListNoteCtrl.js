/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

export default class ListNoteCtrl {
  constructor(service, ClarolineAPIService) {
    this.deck = service.getDeck()
    this.deckNode = service.getDeckNode()
    this.canEdit = service._canEdit
    this.noteTypes = []
    this.sortedNotes = []
    this.IsCardShown = []
    this.colWidth = []
    this.cardLearnings = []

    this.errorMessage = null
    this.errors = []
    this._service = service
    this._apiService = ClarolineAPIService

    this._deleteNote = this._deleteNote.bind(this)
    service.findAllNoteType().then(
      d => {
        this.noteTypes = d.data
        for (let i=0; i<this.noteTypes.length; i++) {
          this.setColWidth(this.noteTypes[i])
          service.findNoteByNoteType(this.noteTypes[i]).then(
            d => {
              this.sortedNotes.push(d.data)
              this.IsCardShown.push(false)
            }
          )
        }
      }
    )
    service.findAllCardLearning(this.deck).then(
      d => this.cardLearnings = d.data
    )
  }

  setColWidth(noteType) {
    const nbrField = noteType.field_labels.length
    const width = Math.floor(10 / nbrField)
    let cols = []
    for (let i=0; i<nbrField-1; i++) {
      cols.push(width)
    }
    cols.push(10 - (nbrField - 1) * width)
    this.colWidth.push(cols)
  }

  maxColspan(note) {
    let nbr = 1
    for (let i=2; i<=note.field_values.length; i++) {
      nbr *= i
    }
    return nbr
  }

  compareFieldValuesById(first, second) {
    if (first.field_label.id < second.field_label.id) {
      return -1
    }
    if (first.field_label.id > second.field_label.id) {
      return 1
    }
    return 0
  }

  getQuestionsFromCard(note, card) {
    const question_labels = card.card_type.questions
    let questions = []
    for(let i=0; i<question_labels.length; i++) {
      for(let j=0; j<note.field_values.length; j++) {
        if(note.field_values[j].field_label.id == question_labels[i].id) {
          questions.push(note.field_values[j])
        }
      }
    }
    return questions
  }

  getAnswersFromCard(note, card) {
    const answer_labels = card.card_type.answers
    let answers = []
    for(let i=0; i<answer_labels.length; i++) {
      for(let j=0; j<note.field_values.length; j++) {
        if(note.field_values[j].field_label.id == answer_labels[i].id) {
          answers.push(note.field_values[j])
        }
      }
    }
    return answers
  }

  resetCard(card) {
    this._service.resetCard(card).then(
      function () {
        for (let i=0; i<this.cardLearnings.length; i++) {
          if (this.cardLearnings[i].card.id == card.id) {
            this.cardLearnings.splice(i, 1)
          }
        }
      }
    )

  }

  suspendCard(card, suspend) {
    this._service.suspendCard(card, suspend).then(
      function () {
        for (let i=0; i<this.cardLearnings.length; i++) {
          if (this.cardLearnings[i].card.id == card.id) {
            this.cardLearnings[i].painful = suspend
          }
        }
      }
    )
  }

  isSuspend(card) {
    for (let i=0; i<this.cardLearnings.length; i++) {
      if (this.cardLearnings[i].card.id == card.id) {
        return this.cardLearnings[i].painful
      }
    }
    return false
  }

  isNew(card) {
    for (let i=0; i<this.cardLearnings.length; i++) {
      if (this.cardLearnings[i].card.id == card.id) {
        return false
      }
    }
    return true
  }

  confirmDeleteNote(note) {
    const url = this.routing.generate('claroline_delete_note', {
      note: note.id
    })

    let note_str = '<p>'
    let warning = ''

    for (let i = 0; i < note.field_values.length; i++) {
      note_str +=  note.field_values[i].field_label.name + ': '
      note_str +=  note.field_values[i].value
      if (i < note.field_values.length - 1) note_str += '<br>'
    }
    note_str += '</p>'

    warning += '<div class="alert alert-warning" role="alert">'
    warning += '<span class="fa fa-warning" aria-hidden="true"></span> '
    warning += this.translate('note.warning')
    warning += '</div>'

    this._apiService.confirm(
        {url},
        this._deleteNote,
        this.translate('note.delete'),
        //this.translate('note.delete', {note: note_str})
        note_str + warning
    )
  }

  translate(key, data = {}) {
    return window.Translator.trans(key, data, 'flashcard')
  }

  _deleteNote(data) {
    const noteId = data
    for (let i=0; i<this.sortedNotes.length; i++) {
      for (let j=0; j<this.sortedNotes[i].length; j++) {
        if (noteId == this.sortedNotes[i][j].id) {
          this.sortedNotes[i].splice(j, 1)
        }
      }
    }
  }
}
