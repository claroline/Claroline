import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'
import {notBlank, number} from '#/main/app/data/types/validators'
import {makeId} from '#/main/core/scaffolding/id'

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
   * Validate a match item.
   *
   * @param {object} item
   *
   * @return {object} the list of item errors
   */
  validate: (item) => {
    const errors = {}

    // penalty greater than 0 and negatives score on solutions
    if (item.penalty && item.penalty > 0 && item.solutions.length > 0 && item.solutions.filter(solution => solution.score < 0).length > 0) {
      errors.warning = trans('match_warning_penalty_and_negative_scores', {}, 'quiz')
    }

    // at least one solution
    if (item.solutions.length === 0) {
      errors.solutions = trans('match_no_solution', {}, 'quiz')
    } else if (undefined !== item.solutions.find(solution => notBlank(solution.score) || number(solution.score))) {
      // each solution should have a valid score
      errors.solutions = trans('match_score_not_valid', {}, 'quiz')
    } else if (undefined === item.solutions.find(solution => solution.score > 0)) {
      // at least one solution with a score that is greater than 0
      errors.solutions = trans('match_no_valid_solution', {}, 'quiz')
    }

    // no blank item data
    if (item.firstSet.find(set => notBlank(set.data, {isHtml: true})) || item.secondSet.find(set => notBlank(set.data, {isHtml: true}))) {
      errors.items = trans('match_item_empty_data_error', {}, 'quiz')
    }

    // empty penalty
    if (notBlank(item.penalty) || number(item.penalty)) {
      errors.items = trans('match_penalty_not_valid', {}, 'quiz')
    }

    return errors
  },

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
  },

  refreshIdentifiers: (item) => {
    item.id = makeId()

    const mapIds = {}

    item.solutions.forEach(solution => {
      mapIds[solution.firstId] = makeId()
      mapIds[solution.secondId] = makeId()
      solution.firstId = mapIds[solution.firstId]
      solution.secondId = mapIds[solution.secondId]
    })

    item.firstSet.forEach(set => set.id = mapIds[set.id])
    item.secondSet.forEach(set => set.id = mapIds[set.id])

    return item
  }
}
