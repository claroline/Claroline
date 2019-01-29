import {trans} from '#/main/app/intl/translation'
import {chain, number, inRange} from '#/main/core/validation'

import {ScoreInput} from '#/main/app/data/types/score/components/input'
import {ScoreTable} from '#/main/app/data/types/score/components/table'

/**
 * Score data type (maybe not a type).
 *
 * Manages score values.
 */
const dataType = {
  name: 'score',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-check-square-o',
    label: trans('score', {}, 'data'),
    description: trans('score_desc', {}, 'data')
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
   * @param {*}  value   - the value to validate
   * @param {object} options - the current score options
   *
   * @return {string} - the first error message if any
   */
  validate: (value, options) => chain(value, options, [number, inRange]),

  /**
   * Custom components for scores rendering.
   */
  components: {
    input: ScoreInput,
    table: ScoreTable
  }
}

export {
  dataType
}
