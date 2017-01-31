/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

export default class EditNoteTypeCtrl {
  constructor(service, $routeParams, $location) {
    this.deck = service.getDeck()
    this.deckNode = service.getDeckNode()
    this.canEdit = service._canEdit
    this.noteType = null
    this.nexturl = $routeParams.nexturl
    this.questionsChecked = []
    this.answersChecked = []
    this.fieldNameForm = []
    this.questionsForm = []
    this.answersForm = []

    this.errorMessage = null
    this.errors = []
    this._service = service
    this.$location = $location

    service.findNoteType($routeParams.id).then(
      d => {
        this.noteType = d.data
        for (let i=0; i<this.noteType.card_types.length; i++) {
          this.questionsChecked.push([])
          this.answersChecked.push([])
          for (let j=0; j<this.noteType.field_labels.length; j++) {
            this.questionsChecked[i][j] = this._isFieldLabelInArray(
              this.noteType.field_labels[j],
              this.noteType.card_types[i].questions)
            this.answersChecked[i][j] = this._isFieldLabelInArray(
              this.noteType.field_labels[j],
              this.noteType.card_types[i].answers)
          }
        }
      }
    )
  }

  addFieldLabel() {
    this.noteType.field_labels.push({
      name: ''
    })
    for (let i=0; i<this.noteType.card_types.length; i++) {
      this.questionsChecked[i].push(false)
      this.answersChecked[i].push(false)
    }
  }

  removeFieldLabel(pos) {
    this.removeFieldLabelFromCardType(this.noteType.field_labels[pos])
    for (let i=0; i<this.noteType.card_types.length; i++) {
      this.questionsChecked[i].splice(pos, 1)
      this.answersChecked[i].splice(pos, 1)
    }
    this.noteType.field_labels.splice(pos, 1)
  }

  removeFieldLabelFromCardType(fieldLabel) {
    for(let i=0; i<this.noteType.card_types.length; i++) {
      for (let j=0; j<this.noteType.card_types[i].questions.length; j++) {
        if (this.noteType.card_types[i].questions[j].name == fieldLabel.name) {
          this.noteType.card_types[i].questions.splice(j, 1)
          this.questionsChecked[i].splice(j, 1)
        }
      }
      for (let j=0; j<this.noteType.card_types[i].answers.length; j++) {
        if (this.noteType.card_types[i].answers[j].name == fieldLabel.name) {
          this.noteType.card_types[i].answers.splice(j, 1)
          this.answersChecked[i].splice(j, 1)
        }
      }
    }
  }

  addCardType() {
    this.noteType.card_types.push({
      name: '',
      questions: [],
      answers: []
    })
    this.questionsChecked.push([])
    this.answersChecked.push([])
  }

  addReverseCardType(pos) {
    this.noteType.card_types.push({
      name: '',
      questions: this.noteType.card_types[pos].answers,
      answers: this.noteType.card_types[pos].questions
    })
    this.questionsChecked.push(this.answersChecked[pos])
    this.answersChecked.push(this.questionsChecked[pos])
  }

  removeCardType(pos) {
    this.noteType.card_types.splice(pos, 1)
    this.questionsChecked.splice(pos, 1)
    this.answersChecked.splice(pos, 1)
  }

  clickQuestion(posCardType, posFieldLabel, fieldLabel) {
    const cardTypes = this.noteType.card_types
    if (this.questionsChecked[posCardType][posFieldLabel]) {
      for (let i=0; i<cardTypes[posCardType].questions.length; i++) {
        if (fieldLabel.name == cardTypes[posCardType].questions[i].name) {
          cardTypes[posCardType].questions.splice(i, 1)
        }
      }
      this.questionsChecked[posCardType][posFieldLabel] = false
    } else {
      cardTypes[posCardType].questions.push(fieldLabel)
      this.questionsChecked[posCardType][posFieldLabel] = true
    }
    this.questionsForm[posCardType].$setValidity(
      'atLeastOne',
      cardTypes[posCardType].questions.length > 0)
  }

  clickAnswer(posCardType, posFieldLabel, fieldLabel) {
    const cardTypes = this.noteType.card_types
    if (this.answersChecked[posCardType][posFieldLabel]) {
      for (let i=0; i<cardTypes[posCardType].answers.length; i++) {
        if (fieldLabel.name == cardTypes[posCardType].answers[i].name) {
          cardTypes[posCardType].answers.splice(i, 1)
        }
      }
      this.answersChecked[posCardType][posFieldLabel] = false
    } else {
      cardTypes[posCardType].answers.push(fieldLabel)
      this.answersChecked[posCardType][posFieldLabel] = true
    }
    this.questionsForm[posCardType].$setValidity(
      'atLeastOne',
      cardTypes[posCardType].answers.length > 0)
  }

  verifyUniqueFieldName() {
    let fieldLabels, isNotUnique
    fieldLabels = this.noteType.field_labels
    isNotUnique = new Array(fieldLabels.length)

    for (let i=0; i<fieldLabels.length; i++) {
      for (let j=i+1; j<fieldLabels.length; j++) {
        if (!isNotUnique[i]) {
          isNotUnique[i] = fieldLabels[i].name == fieldLabels[j].name
        }
        if (!isNotUnique[j]) {
          isNotUnique[j] = fieldLabels[i].name == fieldLabels[j].name
        }
      }
      this.fieldNameForm[i].fieldName.$setValidity('unique', !isNotUnique[i])
    }
  }

  editNoteType(form) {
    if (form.$valid) {
      this._service.editNoteType(this.noteType).then(
        d => {
          this.note = d.data
          this.$location.search('nexturl', null)
          this.$location.path(this.nexturl)
        },
        d => {
          this.errorMessage = 'errors.note_type.creation_failure'
          this.errors = d.data
        }
      )
    }
  }

  _isFieldLabelInArray(field_label, array) {
    let result = false
    for (let i=0; i<array.length && !result; i++) {
      result = (array[i].name == field_label.name)
    }
    return result
  }
}
