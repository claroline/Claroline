import merge from 'lodash/merge'
import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'
import {notBlank, number, chainSync} from '#/main/app/data/types/validators'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/items/utils'

import {PairItem as PairItemType} from '#/plugin/exo/items/pair/prop-types'
import {utils} from '#/plugin/exo/items/pair/utils'
import {makeId} from '#/main/core/scaffolding/id'

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
   * Validate a pair item.
   *
   * @param {object} item
   *
   * @return {object} the list of item errors
   */
  validate: (item) => {
    const errors = {}

    // penalty should be greater or equal to 0
    errors.penalty = chainSync(item.penalty, {}, [notBlank, number])

    // random can not be used if no pinned item
    if (item.random && item.items.filter(pItem => pItem.coordinates && pItem.coordinates.length === 2).length === 0) {
      errors.random = trans('pair_random_needs_pin_item', {}, 'quiz')
    }

    // no blank items / odds
    if (item.items.find(pItem => notBlank(pItem.data, {isHtml: true}))) {
      // item / odd data should not be empty
      errors.solutions = trans('item_empty_data_error', {}, 'quiz')
    }

    // solutions and odd
    if (item.solutions.length > 0) {
      // odd score not empty and valid number
      if (undefined !== item.solutions.find(solution => solution.itemIds.length === 1 && chainSync(solution.score, {}, [notBlank, number]) && solution.score > 0)) {
        errors.odd = trans('odd_score_not_valid', {}, 'quiz')
      }

      // no pair with no score
      if (undefined !== item.solutions.find(solution => solution.itemIds.length === 2 && chainSync(solution.score, {}, [notBlank, number]))) {
        errors.solutions = trans('solution_score_not_valid', {}, 'quiz')
      }

      // no pair with only one item...
      if (undefined !== item.solutions.find(solution => solution.itemIds.length === 2 && solution.itemIds.indexOf(-1) !== -1)) {
        errors.solutions = trans('pair_solution_at_least_two_items', {}, 'quiz')
      }
    }

    return errors
  },

  /**
   * Correct an answer submitted to a pair item.
   *
   * @param {object} item
   * @param {object} answer
   *
   * @return {CorrectedAnswer}
   */
  correctAnswer: (item, answer = {data: []}) => {
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
    const answers = []

    if (item.solutions) {
      if (item.solutions.associations) {
        item.solutions.associations.map(solution => answers.push(new Answerable(solution.score)))
      }

      if (item.solutions.odd) {
        item.solutions.odd.map(odd => answers.push(new Answerable(odd.score)))
      }
    }

    return answers
  },

  refreshIdentifiers: (item) => {
    const mapIds = {}


    item.items.forEach(item => {
      mapIds[item.id] = makeId()
      item.id = mapIds[item.id]
    })

    item.solutions.forEach(solution => solution.itemIds.forEach((itemId, idx) => solution.itemIds[idx] = mapIds[itemId]))
    item.id = makeId()

    return item
  }
}
