/*
 * This file is part of the Claroline Connect package.
 * 
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

export default class StudyCtrl {
  constructor (service, $http) {
    this.deck = service.getDeck()
    this.deckNode = service.getDeckNode()
    this.canEdit = service._canEdit
    this.newCards = []
    this.learningCards = []
    this.sessionId = 0
    this.currentCard = {}
    this.currentCardIsNew = 0
    this.questions = []
    this.answers = []
    this.answerQuality = -1

    this._service = service

    service.findNewCardToLearn(this.deck).then(d => {this.newCards = d.data; this.chooseCard()})
    service.findCardToLearn(this.deck).then(d => this.learningCards = d.data)
  }

  createSession () {
    this._service.createSession().then(d => this.session = d.data)
  }

  chooseCard () {
    // An integer value in range [0; 1[
    let rand = Math.floor(Math.random() * 2)

    this.questions = []
    this.answers = []
    
    if (rand == 0) {
      if (this.newCards.length > 0) {
        rand = Math.floor(Math.random() * this.newCards.length)
        this.currentCard = this.newCards.splice(rand, 1)[0]
        this.currentCardIsNew = 1
        this.showQuestions()
      } else {
        this.chooseCard()
      }
    } else {
      if (this.learningCards.length > 0) {
        rand = Math.floor(Math.random() * this.learningCards.length)
        this.currentCard = this.learningCards.splice(rand, 1)[0]
        this.currentCardIsNew = 0
        this.showQuestions()
      } else {
        this.chooseCard()
      }
    }
  }

  showQuestions () {
    for (let i=0; i < this.currentCard.card_type.questions.length; i++) {
        for (let j=0; j < this.currentCard.note.field_values.length; j++) {
          if (this.currentCard.card_type.questions[i].id ==
              this.currentCard.note.field_values[j].field_label.id) {
            this.questions.push(this.currentCard.note.field_values[j])
          }
        }
    }
  }

  showAnswers () {
    for (let i=0; i < this.currentCard.card_type.answers.length; i++) {
        for (let j=0; j < this.currentCard.note.field_values.length; j++) {
          if (this.currentCard.card_type.answers[i].id ==
              this.currentCard.note.field_values[j].field_label.id) {
            this.answers.push(this.currentCard.note.field_values[j])
          }
        }
    }
  }

  validAnswer (answerQuality) {
    this.answerQuality = answerQuality
    // We need to treat the case where this request doesn't work
    this._service.studyCard(
        this.deck, 
        this.sessionId, 
        this.currentCard, 
        answerQuality).then(d => this.sessionId = d.data);
    this.chooseCard()
  }
}
