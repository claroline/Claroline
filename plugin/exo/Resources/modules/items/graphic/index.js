import {trans} from '#/main/app/intl/translation'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/items/utils'
import {GraphicItem as GraphicItemTypes} from '#/plugin/exo/items/graphic/prop-types'
import {utils} from '#/plugin/exo/items/graphic/utils'

// components
import {GraphicPaper} from '#/plugin/exo/items/graphic/components/paper'
import {GraphicEditor} from '#/plugin/exo/items/graphic/components/editor'
import {GraphicPlayer} from '#/plugin/exo/items/graphic/components/player'
import {GraphicFeedback} from '#/plugin/exo/items/graphic/components/feedback'

// scores
import ScoreSum from '#/plugin/exo/scores/sum'

export default {
  name: 'graphic',
  type: 'application/x.graphic+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: GraphicPaper,
  player: GraphicPlayer,
  feedback: GraphicFeedback,

  components: {
    editor: GraphicEditor
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
  }
}
