import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/items/utils'
import {OrderingItem as OrderingItemType} from '#/plugin/exo/items/ordering/prop-types'

// components
import {OrderingEditor} from '#/plugin/exo/items/ordering/components/editor'
import {OrderingPaper} from '#/plugin/exo/items/ordering/components/paper'
import {OrderingPlayer} from '#/plugin/exo/items/ordering/components/player'
import {OrderingFeedback} from '#/plugin/exo/items/ordering/components/feedback'

// scores
import ScoreFixed from '#/plugin/exo/scores/fixed'
import ScoreSum from '#/plugin/exo/scores/sum'

export default {
  name: 'ordering',
  type: 'application/x.ordering+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  // old
  paper: OrderingPaper,
  player: OrderingPlayer,
  feedback: OrderingFeedback,

  components: {
    editor: OrderingEditor
  },

  /**
   * List all available score modes for an ordering item.
   *
   * @return {Array}
   */
  supportScores: () => [
    ScoreFixed,
    ScoreSum
  ],

  /**
   * Create a new ordering item.
   *
   * @param {object} baseItem
   *
   * @return {object}
   */
  create: (baseItem) => merge({}, OrderingItemType.defaultProps, baseItem),

  /**
   * Correct an answer submitted to a ordering item.
   *
   * @param {object} item
   * @param {object} answers
   *
   * @return {CorrectedAnswer}
   */
  correctAnswer: (item, answers = {data: []}) => {
    const corrected = new CorrectedAnswer()

    item.solutions.forEach(solution => {
      const userAnswer =
        answers && answers.data ?
          answers.data.find(answer => (answer.itemId === solution.itemId) && (answer.position === solution.position)):
          null

      if (userAnswer) {
        corrected.addExpected(new Answerable(solution.score))
      } else {
        corrected.addMissing(new Answerable(solution.score))
        corrected.addPenalty(new Answerable(item.penalty))
      }
    })

    return corrected
  },

  expectAnswer: (item) => {
    if (item.solutions) {
      return item.solutions
        .filter(solution => 0 < solution.score && undefined !== item.position && null !== item.position)
        .map(solution => new Answerable(solution.score, solution.id))
    }

    return []
  },

  allAnswers: (item) => {
    if (item.solutions) {
      return item.solutions.map(solution => new Answerable(solution.score, solution.id))
    }

    return []
  }
}
