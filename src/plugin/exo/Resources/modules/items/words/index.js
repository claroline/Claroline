import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'

import {CorrectedAnswer, Answerable} from '#/plugin/exo/items/utils'
import {WordsItem as WordsItemTypes} from '#/plugin/exo/items/words/prop-types'
import {keywords as keywordsUtils} from '#/plugin/exo/utils/keywords'
import {utils} from '#/plugin/exo/items/words/utils'
import {makeId} from '#/main/core/scaffolding/id'

// components
import {WordsPaper} from '#/plugin/exo/items/words/components/paper'
import {WordsPlayer} from '#/plugin/exo/items/words/components/player'
import {WordsFeedback} from '#/plugin/exo/items/words/components/feedback'
import {WordsEditor} from '#/plugin/exo/items/words/components/editor'

// scores
import ScoreSum from '#/plugin/exo/scores/sum'

export default {
  name: 'words',
  type: 'application/x.words+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: WordsPaper,
  player: WordsPlayer,
  feedback: WordsFeedback,

  components: {
    editor: WordsEditor
  },

  /**
   * List all available score modes for a words item.
   *
   * @return {Array}
   */
  supportScores: () => [
    ScoreSum
  ],

  /**
   * Create a new words item.
   *
   * @param {object} baseItem
   *
   * @return {object}
   */
  create: (baseItem) => Object.assign(baseItem, WordsItemTypes.defaultProps),

  /**
   * Validate a words item.
   *
   * @param {object} item
   *
   * @return {object} the list of item errors
   */
  validate: (item) => {
    const errors = {}

    // Checks keyword collection
    const keywordsErrors = keywordsUtils.validate(item.solutions, true, 1)
    if (!isEmpty(keywordsErrors)) {
      errors.keywords = keywordsErrors
    }

    return errors
  },

  /**
   * Correct an answer submitted to a words item.
   *
   * @param {object} item
   * @param {object} answer
   *
   * @return {CorrectedAnswer}
   */
  correctAnswer: (item, answer = {data: ''}) => {
    const corrected = new CorrectedAnswer()

    item.solutions.forEach(solution => {
      const hasKeyword = utils.containsKeyword(solution.text, solution.caseSensitive, answer ? answer.data: '')

      if (hasKeyword) {
        solution.score > 0 ?
          corrected.addExpected(new Answerable(solution.score)):
          corrected.addUnexpected(new Answerable(solution.score))
      } else {
        if (solution.score > 0) corrected.addMissing(new Answerable(solution.score))
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
    item.id = makeId()
    
    return item
  }
}
