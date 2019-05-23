import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'

import {emptyAnswer, CorrectedAnswer, Answerable} from '#/plugin/exo/items/utils'
import {MatchItem as MatchItemTypes} from '#/plugin/exo/items/match/prop-types'
import {utils} from '#/plugin/exo/items/match/utils'

// components
import {MatchPaper} from '#/plugin/exo/items/match/components/paper'
import {MatchPlayer} from '#/plugin/exo/items/match/components/player'
import {MatchFeedback} from '#/plugin/exo/items/match/components/feedback'
import {MatchEditor} from '#/plugin/exo/items/match/components/editor'

// scores
import ScoreSum from '#/plugin/exo/scores/sum'

export default {
  name: 'match',
  type: 'application/x.match+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: MatchPaper,
  player: MatchPlayer,
  feedback: MatchFeedback,

  components: {
    editor: MatchEditor
  },

  /**
   * List all available score modes for a match item.
   *
   * @return {Array}
   */
  supportScores: () => [
    ScoreSum
  ],

  /**
   * Create a new match item.
   *
   * @param {object} baseItem
   *
   * @return {object}
   */
  create: (baseItem) => Object.assign(baseItem, MatchItemTypes.defaultProps, {
    firstSet: [emptyAnswer()],
    secondSet: [emptyAnswer(), emptyAnswer()]
  }),

  /**
   * Correct an answer submitted to a match item.
   *
   * @param {object} item
   * @param {object} answers
   *
   * @return {CorrectedAnswer}
   */
  correctAnswer: (item, answers = {data: []}) => {
    const corrected = new CorrectedAnswer()

    item.solutions.forEach(solution => {
      const userAnswer = utils.findAnswer(solution, answers.data)

      if (userAnswer) {
        solution.score > 0 ?
          corrected.addExpected(new Answerable(solution.score)):
          corrected.addUnexpected(new Answerable(solution.score))
      } else {
        if (solution.score > 0)
          corrected.addMissing(new Answerable(solution.score))
      }
    })

    const answersCount = answers && answers.data ? answers.data.length: 0
    times(item.solutions.filter(solution => solution.score > 0).length - answersCount, () => corrected.addPenalty(new Answerable(item.penalty)))

    return corrected
  },

  expectAnswer: (item) => {
    if (item.solutions && item.solutions.associations) {
      return item.solutions.associations
        .filter(solution => 0 < solution.score)
        .map(solution => new Answerable(solution.score, solution.id))
    }

    return []
  },

  allAnswers: (item) => {
    if (item.solutions && item.solutions.associations) {
      return item.solutions.associations.map(solution => new Answerable(solution.score))
    }

    return []
  }
}
