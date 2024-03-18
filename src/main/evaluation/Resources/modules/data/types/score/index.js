import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {chain, number, inRange} from '#/main/app/data/types/validators'

import {displayScore} from '#/main/evaluation/data/types/score/utils'
import {ScoreCell} from '#/main/evaluation/data/types/score/components/cell'
import {ScoreDisplay} from '#/main/evaluation/data/types/score/components/display'

/**
 * Score data type.
 *
 * Manages score values.
 */
const dataType = {
  name: 'score',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-check-square',
    label: trans('score', {}, 'data'),
    description: trans('score_desc', {}, 'data')
  },

  parse: (display) => parseFloat(display),

  /**
   * Displays a score value.
   *
   * @param {{current: number, total: number}} raw
   *
   * @return {string}
   */
  render: (raw) => {
    if (!isEmpty(raw)) {
      return displayScore(raw.total, raw.current, raw.display)
    }

    return ''
  },

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
    // old api
    details: ScoreDisplay,
    table: ScoreCell,

    // new api
    display: ScoreDisplay,
    cell: ScoreCell
  }
}

export {
  dataType
}
