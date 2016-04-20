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
    this.fieldValues = []
    this.newCards = []
    this.againCards = []
    this.learningCards = []
    this.session = {}
    this.currentCard = {}
    this.questions = []
    this.answers = []
    this.answerQuality = -1


    service.findNewCardToLearn(this.deck).then(d => {this.newCards = d.data; this.chooseCard()})
    service.findCardToLearn(this.deck).then(d => this.learningCards = d.data)
  }

  createSession () {
    this._service.createSession().then(d => this.session = d.data)
  }

  chooseCard () {
    // An integer value in range [0; 2[
    let rand = Math.floor(Math.random() * 3)
    
    if (rand == 1) {
      if (this.newCards.length > 0) {
        rand = Math.floor(Math.random() * this.newCards.length)
        this.currentCard = this.newCards.slice(rand, rand+1)[0]
        this.showQuestions()
      } else {
        this.chooseCard()
      }
    } else if (rand == 2) {
      if (this.againCards.length > 0) {
        rand = Math.floor(Math.random() * this.againCards.length)
        this.currentCard = this.againCards.slice(rand, rand+1)[0]
        this.showQuestions()
      } else {
        this.chooseCard()
      }
    } else {
      if (this.learningCards.length > 0) {
        rand = Math.floor(Math.random() * this.learningCards.length)
        this.currentCard = this.learningCards.slice(rand, rand+1)[0]
        this.showQuestions()
      } else {
        this.chooseCard()
      }
    }
  }

  showQuestions () {
    for (let i=0; i < this.currentCard.card.card_type.questions.length; i++) {
      //if (this.currentCard.card.note) {
        for (let j=0; j < this.currentCard.card.note.field_values.length; j++) {
          if (this.currentCard.card.card_type.questions[i].id ==
              this.currentCard.card.note.field_values[j].field_label.id) {
            this.questions.push(this.currentCard.card.note.field_values[j])
          }
        }
      //}
    }
  }

  showAnswers () {
    for (let i=0; i < this.currentCard.card.card_type.answers.length; i++) {
      //if (this.currentCard.card.note) {
        for (let j=0; j < this.currentCard.card.note.field_values.length; j++) {
          if (this.currentCard.card.card_type.answers[i].id ==
              this.currentCard.card.note.field_values[j].field_label.id) {
            this.answers.push(this.currentCard.card.note.field_values[j])
          }
        }
      //}
    }
  }
}
