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
    this.canEdit= service._canEdit
    this.newCards = []
    this.learingCards = []

    this._service = service

    service.findNewCardToLearn(this.deck).then(d => this.newCards = d.data)
    service.findCardToLearn(this.deck).then(d => this.learningCards = d.data)
  }
}
