import merge from 'lodash/merge'

import {notBlank, number, gteZero, chainSync} from '#/main/app/data/types/validators'

import {CorrectedAnswer} from '#/plugin/exo/items/utils'
import {OpenItem} from '#/plugin/exo/items/open/prop-types'

// components
import {OpenPlayer} from '#/plugin/exo/items/open/components/player'
import {OpenFeedback} from '#/plugin/exo/items/open/components/feedback'
import {OpenEditor} from '#/plugin/exo/items/open/components/editor'

// scores
import ScoreManual from '#/plugin/exo/scores/manual'

export default {
  name: 'open',
  type: 'application/x.open+json',
  answerable: true,

  components: {
    editor: OpenEditor,
    player: OpenPlayer,
    feedback: OpenFeedback
  },

  /**
   * List all available score modes for an open item.
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
   * Validate an open item.
   *
   * @param {object} item
   *
   * @return {object} the list of item errors
   */
  validate: (item) => {
    const errors = {}

    if (item._restrictLength) errors.maxLength = chainSync(item.maxLength, {}, [notBlank, number, gteZero])

    return errors
  },

  /**
   * Correct an answer submitted to an open item.
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
