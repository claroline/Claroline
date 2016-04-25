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
  constructor ($http) {
    this.$http = $http
    this._deck = FlashCardService._getGlobal('deck')
    this._deckNode = FlashCardService._getGlobal('deckNode')
  }

  getDeck () {
    return this._deck
  }

  getDeckNode () {
    return this._deckNode
  }

  getNoteTypes () {
    return this._noteTypes
  }

  findAllNoteType () {
    const url = Routing.generate('claroline_getall_note_type')
    return this.$http.get(url)
  }

  findNewCardToLearn (deck) {
    const url = Routing.generate('claroline_new_card_to_learn', {
      deck: deck.id
    })
    return this.$http.get(url)
  }

  findCardToLearn (deck) {
    const url = Routing.generate('claroline_card_to_review', {
      deck: deck.id
    })
    return this.$http.get(url)
  }

  createNote (noteType, fields) {
    const url = Routing.generate('claroline_create_note', {
      deck: this._deck.id,
      noteType: noteType.id
    })

    return this.$http
      .post(url, { fields: fields})
  }

  findNoteByNoteType (noteType) {
    const url = Routing.generate('claroline_list_notes', {
      deck: this._deck.id,
      noteType: noteType.id
    })

    return this.$http.get(url)
  }

  createSession () {
    const url = Routing.generate('claroline_create_session')
    return this.$http.get(url)
  }

  studyCard (deck, sessionId, card, answerQuality) {
    const url = Routing.generate('claroline_study_card', {
      deck: deck.id,
      sessionId: sessionId,
      card: card.id,
      result: answerQuality,
    })
    return this.$http.get(url)
  }

  static _getGlobal (name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }
}
