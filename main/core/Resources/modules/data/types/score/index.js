import {t} from '#/main/core/translation'
import {chain, number, inRange} from '#/main/core/validation'

import {ScoreForm} from '#/main/core/data/types/score/components/form.jsx'
import {ScoreTable} from '#/main/core/data/types/score/components/table.jsx'

const SCORE_TYPE = 'score'

/**
 * Score definition.
 * Manages score values.
 */
const scoreDefinition = {
  meta: {
    type: SCORE_TYPE,
    creatable: false,
    icon: 'fa fa-fw fa fa-check-square-o',
    label: t('score'),
    description: t('score_desc')
  },

  parse: (display) => parseFloat(display),

  /**
   * Displays a score value.
   *
   * @param {number}  raw
   * @param {options} options
   *
   * @return {string}
   */
  render: (raw, options) => (raw || 0 === raw ? raw : '-') + ' / ' + options.max,

  /**
   * Validates a score value.
   *
   * @param {mixed}  value   - the value to validate
   * @param {object} options - the current score options
   *
   * @return {string} - the first error message if any
   */
  validate: (value, options) => chain(value, options, [number, inRange]),

  /**
   * Custom components for scores rendering.
   */
  components: {
    form: ScoreForm,
    table: ScoreTable
  }
}

export {
  SCORE_TYPE,
  scoreDefinition
}
