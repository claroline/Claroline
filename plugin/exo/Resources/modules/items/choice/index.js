import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

import {ChoiceItem} from '#/plugin/exo/items/choice/prop-types'

// components
import {ChoiceEditor} from '#/plugin/exo/items/choice/components/editor'
import {ChoiceFeedback} from '#/plugin/exo/items/choice/components/feedback'
import {ChoicePaper} from '#/plugin/exo/items/choice/components/paper'
import {ChoicePlayer} from '#/plugin/exo/items/choice/components/player'

// scores
import ScoreFixed from '#/plugin/exo/scores/fixed'
import ScoreRules from '#/plugin/exo/scores/rules'
import ScoreSum from '#/plugin/exo/scores/sum'

export default {
  name: 'choice',
  type: 'application/x.choice+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: ChoicePaper,
  player: ChoicePlayer,
  feedback: ChoiceFeedback,

  components: {
    editor: ChoiceEditor
  },

  /**
   * List all available score modes for a choice item.
   *
   * @param {object} item
   *
   * @return {Array}
   */
  supportScores: (item) => {
    const supportedScores = [
      ScoreFixed,
      ScoreSum
    ]

    if (item.multiple) {
      supportedScores.push(ScoreRules)
    }

    return supportedScores
  },

  /**
   * Create a new choice item.
   *
   * @param {object} baseItem
   *
   * @return {object}
   */
  create: (baseItem) => {
    // append default choice props
    const choiceItem = merge({}, baseItem, ChoiceItem.defaultProps)

    // create 2 empty choices
    const firstChoiceId = makeId()
    const secondChoiceId = makeId()

    choiceItem.choices = [
      {
        id: firstChoiceId,
        type: 'text/html',
        data: ''
      }, {
        id: secondChoiceId,
        type: 'text/html',
        data: ''
      }
    ]

    // create solutions for choices
    choiceItem.solutions = [
      {
        id: firstChoiceId,
        score: 1,
        feedback: ''
      }, {
        id: secondChoiceId,
        score: 0,
        feedback: ''
      }
    ]

    return choiceItem
  },

  /**
   * Correct an answer submitted to a choice item.
   *
   * @param {object} item
   * @param {object} answers
   *
   * @return {CorrectedAnswer}
   */
  getCorrectedAnswer: (item, answers = null) => {
    const corrected = new CorrectedAnswer()

    item.solutions.forEach(choice => {
      const score = choice.score

      if (answers && answers.data && answers.data.indexOf(choice.id) > -1) {
        score > 0 ?
          corrected.addExpected(new Answerable(score)) :
          corrected.addUnexpected(new Answerable(score))
      } else {
        if (score > 0) {
          corrected.addMissing(new Answerable(score))
        } else {
          corrected.addExpectedMissing(new Answerable(score))
        }
      }
    })

    return corrected
  }
}
