/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

export default class FlashCardService {
  constructor($http) {
    this.$http = $http
    this._deck = FlashCardService._getGlobal('deck')
    this._deckNode = FlashCardService._getGlobal('deckNode')
    this._canEdit = FlashCardService._getGlobal('canEdit')
    this.routing = window.Routing
  }

  getDeck() {
    return this._deck
  }

  getDeckNode() {
    return this._deckNode
  }

  getNoteTypes() {
    return this._noteTypes
  }

  findAllNoteType() {
    const url = this.routing.generate('claroline_getall_note_type')
    return this.$http.get(url)
  }

  countCards(deck) {
    const url = this.routing.generate('claroline_count_cards', {
      deck: deck.id
    })
    return this.$http.get(url)
  }

  findNewCardToLearn(deck) {
    const url = this.routing.generate('claroline_new_card_to_learn', {
      deck: deck.id
    })
    return this.$http.get(url)
  }

  findCardToLearn(deck) {
    const url = this.routing.generate('claroline_card_to_review', {
      deck: deck.id
    })
    return this.$http.get(url)
  }

  findAllCardLearning(deck) {
    const url = this.routing.generate('claroline_getall_card_learning', {
      deck: deck.id
    })
    return this.$http.get(url)
  }

  countCardLearning(deck) {
    const url = this.routing.generate('claroline_count_card_learning', {
      deck: deck.id
    })
    return this.$http.get(url)
  }

  createNote(noteType, fields) {
    const url = this.routing.generate('claroline_create_note', {
      deck: this._deck.id,
      noteType: noteType.id
    })

    return this.$http
      .post(url, { fields: fields})
  }

  editNote(note, fieldValues) {
    const url = this.routing.generate('claroline_edit_note', {
      note: note.id
    })

    return this.$http.post(url, { fieldValues: fieldValues })
  }

  findNote(id) {
    const url = this.routing.generate('claroline_get_note', {
      note: id
    })

    return this.$http.get(url)
  }

  findNoteByNoteType(noteType) {
    const url = this.routing.generate('claroline_list_notes', {
      deck: this._deck.id,
      noteType: noteType.id
    })

    return this.$http.get(url)
  }

  findNoteType(id) {
    const url = this.routing.generate('claroline_get_note_type', {
      noteTypeId: id
    })

    return this.$http.get(url)
  }

  editNoteType(noteType) {
    const url = this.routing.generate('claroline_edit_note_type')

    return this.$http.post(url, { noteType: noteType })
  }

  createSession() {
    const url = this.routing.generate('claroline_create_session')
    return this.$http.get(url)
  }

  studyCard(deck, sessionId, card, answerQuality) {
    const url = this.routing.generate('claroline_study_card', {
      deck: deck.id,
      sessionId: sessionId,
      card: card.id,
      result: answerQuality
    })
    return this.$http.get(url)
  }

  cancelLastStudy(deck, sessionId, card) {
    const url = this.routing.generate('claroline_cancel_last_study', {
      deck: deck.id,
      sessionId: sessionId,
      card: card.id
    })
    return this.$http.get(url)
  }

  suspendCard(card, suspend) {
    const url = this.routing.generate('claroline_suspend_card', {
      card: card.id,
      suspend: suspend
    })
    return this.$http.get(url)
  }

  resetCard(card) {
    const url = this.routing.generate('claroline_reset_card', {
      card: card.id
    })
    return this.$http.get(url)
  }

  getAllThemes() {
    const url = this.routing.generate('claroline_get_all_themes')
    return this.$http.get(url)
  }

  editDefaultParam(deck, newCardDay, theme) {
    const url = this.routing.generate('claroline_edit_default_param', {
      deck: deck.id
    })

    return this.$http.post(url,
      {
        newCardDay: newCardDay,
        theme: theme
      })
  }

  editUserParam(deck, newCardDay, theme) {
    const url = this.routing.generate('claroline_edit_user_param', {
      deck: deck.id
    })

    return this.$http.post(url,
      {
        newCardDay: newCardDay,
        theme: theme
      })
  }

  getUserPreference(deck) {
    const url = this.routing.generate('claroline_get_user_pref', {
      deck: deck.id
    })

    return this.$http.get(url)
  }

  static _getGlobal(name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }
}
