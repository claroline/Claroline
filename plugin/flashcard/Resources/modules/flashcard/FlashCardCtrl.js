/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

export default class FlashCardCtrl {
  constructor(service) {
    this.deck = service.getDeck()
    this.deckNode = service.getDeckNode()
    this.canEdit = service._canEdit
    this.newCards = []
    this.learningCards = []
    this.nbrTotalCards = 0
    this.nbrRevisedCards = 0

    this._service = service

    service.countCards(this.deck).then(d => this.nbrTotalCards = d.data)
    service.countCardLearning(this.deck).then(d => this.nbrRevisedCards = d.data)
    service.findNewCardToLearn(this.deck).then(d => this.newCards = d.data)
    service.findCardToLearn(this.deck).then(d => this.learningCards = d.data)
  }
}
