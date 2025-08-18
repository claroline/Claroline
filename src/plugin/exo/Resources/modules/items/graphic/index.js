import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/app/utils/id'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/items/utils'

import {GraphicItem as GraphicItemTypes} from '#/plugin/exo/items/graphic/prop-types'
import {utils} from '#/plugin/exo/items/graphic/utils'
import {MAX_IMG_SIZE} from '#/plugin/exo/items/graphic/constants'

// components
import {GraphicEditor} from '#/plugin/exo/items/graphic/components/editor'
import {GraphicExpectedAnswer} from '#/plugin/exo/items/graphic/components/expected-answer'
import {GraphicFeedback} from '#/plugin/exo/items/graphic/components/feedback'
import {GraphicPlayer} from '#/plugin/exo/items/graphic/components/player'
import {GraphicStats} from '#/plugin/exo/items/graphic/components/stats'

// scores
import ScoreSum from '#/plugin/exo/scores/sum'

export default {
  name: 'graphic',
  type: 'application/x.graphic+json',
  answerable: true,

  components: {
    editor: GraphicEditor,
    player: GraphicPlayer,
    feedback: GraphicFeedback,
    expectedAnswer: GraphicExpectedAnswer,
    stats: GraphicStats
  },

  /**
   * List all available score modes for a graphic item.
   *
   * @return {Array}
   */
  supportScores: () => [
    ScoreSum
  ],

  /**
   * Create a new graphic item.
   *
   * @param {object} baseItem
   *
   * @return {object}
   */
  create: (baseItem) => Object.assign(baseItem, GraphicItemTypes.defaultProps),

  /**
   * Validate a graphic item.
   *
   * @param {object} item
   *
   * @return {object} the list of item errors
   */
  validate: (item) => {
    if (item.image.type && item.image.type.indexOf('image') !== 0) {
      return {image: trans('graphic_error_not_an_image', {}, 'quiz')}
    }

    if (item.image._size && item.image._size > MAX_IMG_SIZE) {
      return {image: trans('graphic_error_image_too_large', {}, 'quiz')}
    }

    if (!item.image.data && !item.image.url) {
      return {image: trans('graphic_error_no_image', {}, 'quiz')}
    }

    if (item.solutions.length === 0) {
      return {image: trans('graphic_error_no_solution', {}, 'quiz')}
    }

    if (!item.solutions.find(solution => solution.score > 0)) {
      return {image: trans('graphic_error_no_positive_solution', {}, 'quiz')}
    }

    return {}
  },

  /**
   * Correct an answer submitted to a graphic item.
   *
   * @param {object} item
   * @param {object} answers
   *
   * @return {CorrectedAnswer}
   */
  correctAnswer: (item, answers = null) => {
    const corrected = new CorrectedAnswer()

    item.solutions.forEach(solution => {
      if (answers && answers.data) {
        answers.data.forEach(coords => {
          if (utils.isPointInArea(solution.area, coords.x, coords.y)) {
            solution.score > 0 ?
              corrected.addExpected(new Answerable(solution.score, solution.area.id)):
              corrected.addUnexpected(new Answerable(solution.score, solution.area.id))
          } else if (solution.score > 0) {
            corrected.addMissing(new Answerable(solution.score, solution.area.id))
          }
        })
      } else {
        corrected.addMissing(new Answerable(solution.score, solution.area.id))
      }
    })

    return corrected
  },

  expectAnswer: (item) => {
    if (item.solutions) {
      return item.solutions
        .filter(solution => 0 < solution.score)
        .map(solution => new Answerable(solution.score))
    }

    return []
  },

  allAnswers: (item) => {
    if (item.solutions) {
      return item.solutions
        .map(solution => new Answerable(solution.score))
    }

    return []
  },

  refreshIdentifiers: (item) => {
    item.solutions.forEach(solution => solution.area.id = makeId())
    item.image.id = makeId()

    return item
  }
}
