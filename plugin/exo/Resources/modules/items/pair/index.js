import merge from 'lodash/merge'
import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/quiz/correction/components/corrected-answer'

import {PairItem as PairItemType} from '#/plugin/exo/items/pair/prop-types'
import {utils} from '#/plugin/exo/items/pair/utils'

// components
import {PairEditor} from '#/plugin/exo/items/pair/components/editor'
import {PairPaper} from '#/plugin/exo/items/pair/components/paper'
import {PairPlayer} from '#/plugin/exo/items/pair/components/player'
import {PairFeedback} from '#/plugin/exo/items/pair/components/feedback'

// scores
import ScoreSum from '#/plugin/exo/scores/sum'

export default {
  name: 'pair',
  type: 'application/x.pair+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  // old
  paper: PairPaper,
  player: PairPlayer,
  feedback: PairFeedback,

  components: {
    editor: PairEditor
  },

  /**
   * List all available score modes for a pair item.
   *
   * @return {Array}
   */
  supportScores: () => [
    ScoreSum
  ],

  /**
   * Create a new pair item.
   *
   * @param {object} baseItem
   *
   * @return {object}
   */
  create: (baseItem) => merge({}, PairItemType.defaultProps, baseItem),

  /**
   * Correct an answer submitted to a pair item.
   *
   * @param {object} item
   * @param {object} answer
   *
   * @return {CorrectedAnswer}
   */
  getCorrectedAnswer: (item, answer = {data: []}) => {
    const corrected = new CorrectedAnswer()

    //look for good answers
    item.solutions.forEach(solution => {
      const userAnswer = answer && answer.data ? utils.findUserAnswer(solution, answer): null

      if (userAnswer) {
        solution.score > 0 ?
          corrected.addExpected(new Answerable(solution.score)):
          corrected.addUnexpected(new Answerable(solution.score))
      } else {
        if (solution.score > 0) corrected.addMissing(new Answerable(solution.score))
      }
    })

    item.solutions.filter(solution => solution.itemIds.length === 1).forEach(oddity => {
      if (answer && answer.data) {
        const found = answer.data.find(answer => answer.indexOf(oddity.itemIds[0]) >= 0)
        if (found) {
          corrected.addPenalty(new Answerable(-oddity.score))
        }
      }
    })

    if (answer && answer.data) {
      times(item.solutions.filter(solution => solution.score > 0).length - answer.data.length, () => corrected.addPenalty(new Answerable(item.penalty)))
    }

    return corrected
  }
}
