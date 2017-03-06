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
  constructor(service) {
    this.deck = service.getDeck()
    this.deckNode = service.getDeckNode()
    this.canEdit = service._canEdit
    this.initialNbrOfCards = 0
    this.newCards = []
    this.initialNbrOfNewCards = 0
    this.learningCards = []
    this.initialNbrOfLearningCards = 0
    // Revised cards during this session
    this.revisedCards = []
    this.sessionId = 0
    this.currentCard = false
    this.currentCardIsNew = 0
    this.fieldValues = []
    this.questions = []
    this.answers = []
    this.answersShown = false
    this.answerQuality = -1

    this.fullscreenClass = ''
    this.fullscreenClassButton = 'fa-expand'
    this.fullscreenClassFooter = ''
    this.flippedClass = ''

    this._service = service

    service.findNewCardToLearn(this.deck).then(
      d => {
        this.newCards = d.data
        this.initialNbrOfNewCards = this.newCards.length
        this.initialNbrOfCards += this.newCards.length
        if (!this.currentCard) {
          this.chooseCard()
        }
      }
    )
    service.findCardToLearn(this.deck).then(
      d => {
        this.learningCards = d.data
        this.initialNbrOfLearningCards = this.learningCards.length
        this.initialNbrOfCards += this.learningCards.length
        if (!this.currentCard) {
          this.chooseCard()
        }
      }
    )
  }

  createSession() {
    this._service.createSession().then(d => this.session = d.data)
  }

  chooseCard() {
    // An integer value in range [0; 1[
    let rand = Math.floor(Math.random() * 2)

    this.questions = []
    this.answers = []

    if (this.newCards.length == 0 && this.learningCards.length == 0) {
      this.currentCard = false
    } else {
      if (rand == 0) {
        if (this.newCards.length > 0) {
          rand = Math.floor(Math.random() * this.newCards.length)
          this.currentCard = this.newCards.splice(rand, 1)[0]
          this.currentCardIsNew = 1
          this.showQuestions()
          this.showAnswers()
        } else {
          this.chooseCard()
        }
      } else {
        if (this.learningCards.length > 0) {
          rand = Math.floor(Math.random() * this.learningCards.length)
          this.currentCard = this.learningCards.splice(rand, 1)[0]
          this.currentCardIsNew = 0
          this.showQuestions()
          this.showAnswers()
        } else {
          this.chooseCard()
        }
      }
    }
  }

  showQuestions() {
    this.questions = []
    for (let i=0; i < this.currentCard.card_type.questions.length; i++) {
      for (let j=0; j < this.currentCard.note.field_values.length; j++) {
        if (this.currentCard.card_type.questions[i].id ==
            this.currentCard.note.field_values[j].field_label.id) {
          this.questions.push(this.currentCard.note.field_values[j])
        }
      }
    }
  }

  showAnswers() {
    this.answers = []
    for (let i=0; i < this.currentCard.card_type.answers.length; i++) {
      for (let j=0; j < this.currentCard.note.field_values.length; j++) {
        if (this.currentCard.card_type.answers[i].id ==
            this.currentCard.note.field_values[j].field_label.id) {
          this.answers.push(this.currentCard.note.field_values[j])
        }
      }
    }
  }

  validAnswer(answerQuality) {
    this.answerQuality = answerQuality
    // We need to treat the case where this request doesn't work
    this._service.studyCard(
      this.deck,
      this.sessionId,
      this.currentCard,
      answerQuality
    ).then(
      d => {
        this.sessionId = d.data
        this.revisedCards.push(this.currentCard)
        this.chooseCard()
      }
    )
    this.flipCard()
  }

  cancelLastStudy() {
    this._service.cancelLastStudy(
      this.deck,
      this.sessionId,
      this.revisedCards[this.revisedCards.length - 1]
    ).then(
        d => {
          this.sessionId = d.data
        }
    )
    if (this.currentCardIsNew) {
      this.newCards.push(this.currentCard)
      this.currentCard = this.revisedCards.pop()
      this.currentCardIsNew = 0
    } else {
      this.learningCards.push(this.currentCard)
      this.currentCard = this.revisedCards.pop()
    }
    this.showQuestions()
  }

  toggleFullscreen() {
    if (this.fullscreenClass) {
      this.fullscreenClass = ''
      this.fullscreenClassButton = 'fa-expand'
      this.fullscreenClassFooter = ''
    } else {
      this.fullscreenClass = 'fullscreen'
      this.fullscreenClassButton = 'fa-compress'
      this.fullscreenClassFooter = 'footer-fullscreen'
    }
  }

  flipCard() {
    if (this.answersShown) {
      this.flippedClass = ''
    } else {
      this.flippedClass = 'flipped'
    }
    this.answersShown = !this.answersShown
  }
}
