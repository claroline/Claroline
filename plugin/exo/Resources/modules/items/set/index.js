import merge from 'lodash/merge'
import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'
import {notBlank, number, chain} from '#/main/core/validation'
import {makeId} from '#/main/core/scaffolding/id'

import {emptyAnswer, CorrectedAnswer, Answerable} from '#/plugin/exo/items/utils'
import {SetItem as SetItemType} from '#/plugin/exo/items/set/prop-types'

// components
import {SetEditor} from '#/plugin/exo/items/set/components/editor'
import {SetPaper} from '#/plugin/exo/items/set/components/paper'
import {SetPlayer} from '#/plugin/exo/items/set/components/player'
import {SetFeedback} from '#/plugin/exo/items/set/components/feedback'

// scores
import ScoreSum from '#/plugin/exo/scores/sum'

export default {
  name: 'set',
  type: 'application/x.set+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  // old
  paper: SetPaper,
  player: SetPlayer,
  feedback: SetFeedback,

  components: {
    editor: SetEditor
  },

  /**
   * List all available score modes for a set item.
   *
   * @return {Array}
   */
  supportScores: () => [
    ScoreSum
  ],

  /**
   * Create a new set item.
   *
   * @param {object} baseItem
   *
   * @return {object}
   */
  create: (baseItem) => merge({}, baseItem, SetItemType.defaultProps, {
    sets: [emptyAnswer()],
    items: [emptyAnswer()]
  }),

  /**
   * Validate a set item.
   *
   * @param {object} item
   *
   * @return {object} the list of item errors
   */
  validate: (item) => {
    const errors = {}

    // penalty should be greater or equal to 0
    errors.penalty = chain(item.penalty, {}, [notBlank, number])

    // one item (that is not an odd) min
    if (item.items.filter(el => undefined === item.solutions.odd.find(odd => odd.itemId === el.id)).length === 0) {
      errors.items = trans('set_at_least_one_item', {}, 'quiz')
    } else if (item.items.filter(el => undefined === item.solutions.odd.find(odd => odd.itemId === el.id)).find(item => notBlank(item.data, {isHtml: true}))) {
      // item data should not be empty
      errors.items = trans('set_item_empty_data_error', {}, 'quiz')
    }

    // no item (that are not odd items) should be orphan (ie not used in any set)
    if (item.items.some(el => item.solutions.associations.find(association => association.itemId === el.id) === undefined && item.solutions.odd.find(o => o.itemId === el.id) === undefined)) {
      errors.items = trans('set_no_orphean_items', {}, 'quiz')
    }

    // one set min
    if (item.sets.length === 0) {
      errors.sets = trans('set_at_least_one_set', {}, 'quiz')
    }

    if (item.solutions.associations.length === 0) {
      errors.solutions = trans('set_no_solution', {}, 'quiz')
    } else if (undefined !== item.solutions.associations.find(association => chain(association.score, {}, [notBlank, number]))) {
      // each solution should have a valid score
      errors.solutions = trans('set_score_not_valid', {}, 'quiz')
    } else if (undefined === item.solutions.associations.find(association => association.score > 0)) {
      // at least one solution with a score that is greater than 0
      errors.solutions = trans('set_no_valid_solution', {}, 'quiz')
    }

    // odd
    if (item.solutions.odd.length > 0) {
      // odd score not empty and valid number
      if(undefined !== item.solutions.odd.find(odd => chain(odd.score, {}, [notBlank, number]))) {
        errors.odd = trans('set_score_not_valid', {}, 'quiz')
      } else if (undefined !== item.solutions.odd.find(odd => odd.score > 0)) {
        errors.odd = trans('set_odd_score_not_valid', {}, 'quiz')
      }
      // odd data not empty
      if (item.items.filter(el => undefined !== item.solutions.odd.find(odd => odd.itemId === el.id)).find(odd => notBlank(odd.data, {isHtml: true}))) {
        // set data should not be empty
        errors.odd = trans('set_odd_empty_data_error', {}, 'quiz')
      }
    }

    return errors
  },

  /**
   * Correct an answer submitted to a set item.
   *
   * @param {object} item
   * @param {object} answer
   *
   * @return {CorrectedAnswer}
   */
  correctAnswer: (item, answer = {data: []}) => {
    const corrected = new CorrectedAnswer()

    item.solutions.associations.forEach(association => {
      const userAnswer = answer && answer.data ? answer.data.find(answer => (answer.itemId === association.itemId) && (answer.setId === association.setId)): null

      userAnswer ?
        corrected.addExpected(new Answerable(association.score)):
        corrected.addMissing(new Answerable(association.score))
    })

    item.solutions.odd.forEach(odd => {
      const penalty = answer && answer.data ? answer.data.find(answer => answer.itemId === odd.itemId): null
      if (penalty) {
        corrected.addPenalty(new Answerable(Math.abs(odd.score)))
      }
    })

    const found = answer && answer.data ? answer.data.length: 0
    if (item.penalty && 0 !== item.solutions.associations.length - found) {
      times(item.solutions.associations.length - found, () => corrected.addPenalty(new Answerable(item.penalty)))
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
    item.id = makeId()

    const mapIds = {}

    item.items.forEach(item => {
      mapIds[item.id] = makeId()
      item.id = mapIds[item.id]
    })

    item.sets.forEach(set => {
      mapIds[set.id] = makeId()
      set.id = mapIds[set.id]
    })

    item.solutions.associations.forEach(association => {
      association.itemId = mapIds[association.itemId]
      association.setId = mapIds[association.setId]
    })

    item.solutions.odd.forEach(odd => {
      odd.itemId = mapIds[odd.itemId]
    })

    return item
  }
}
