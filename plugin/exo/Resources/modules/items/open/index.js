import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {notBlank, number, gteZero, chain} from '#/main/core/validation'

import {CorrectedAnswer} from '#/plugin/exo/items/utils'
import {OpenItem} from '#/plugin/exo/items/open/prop-types'

// components
import {OpenPaper} from '#/plugin/exo/items/open/components/paper'
import {OpenPlayer} from '#/plugin/exo/items/open/components/player'
import {OpenFeedback} from '#/plugin/exo/items/open/components/feedback'
import {OpenEditor} from '#/plugin/exo/items/open/components/editor'

// scores
import ScoreManual from '#/plugin/exo/scores/manual'

export default {
  name: 'open',
  type: 'application/x.open+json',
  tags: [trans('question', {}, 'quiz')],
  answerable: true,

  paper: OpenPaper,
  player: OpenPlayer,
  feedback: OpenFeedback,

  components: {
    editor: OpenEditor
  },

  /**
   * List all available score modes for a open item.
   *
   * @return {Array}
   */
  supportScores: () => [
    ScoreManual
  ],

  /**
   * Create a new open item.
   *
   * @param {object} baseItem
   *
   * @return {object}
   */
  create: (baseItem) => merge({}, baseItem, OpenItem.defaultProps),

  /**
   * Validate a open item.
   *
   * @param {object} item
   *
   * @return {object} the list of item errors
   */
  validate: (item) => {
    const errors = {}

    if (item._restrictLength) errors.maxLength = chain(item.maxLength, {}, [notBlank, number, gteZero])

    return errors
  },

  /**
   * Correct an answer submitted to a open item.
   *
   * @return {CorrectedAnswer}
   */
  correctAnswer: () => new CorrectedAnswer(),

  expectAnswer: () => [],
  allAnswers: () => [],

  refreshIdentifiers: (item) => {
    return item
  }
}
