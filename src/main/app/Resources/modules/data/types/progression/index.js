import {trans, number as numberFormat} from '#/main/app/intl'
import {chain, number, inRange} from '#/main/app/data/types/validators'

import {ProgressionDisplay} from '#/main/app/data/types/progression/components/display'
import {ProgressionCell} from '#/main/app/data/types/progression/components/cell'
import {declareDataType} from '#/main/app/data/types'
import {NumberFilter} from '#/main/app/data/types/number/components/filter'

/**
 * Progression data type.
 */
export default declareDataType({
  name: 'progression',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-percent',
    label: trans('progression', {}, 'data'),
    description: trans('progression_desc', {}, 'data')
  },

  /**
   * Displays a progression value.
   *
   * @param {number} raw
   *
   * @return {string}
   */
  render: (raw) => numberFormat(raw) + ' %',

  /**
   * Validates a progression value.
   *
   * @param {*}  value       - the value to validate
   * @param {object} options - the current progression options
   *
   * @return {string} - the first error message if any
   */
  validate: (value, options) => chain(value, Object.assign({}, options, {min: 0, max: 100}), [number, inRange]),

  /**
   * Custom components for progression rendering.
   */
  components: {
    display: ProgressionDisplay,
    cell: ProgressionCell,
    filter: NumberFilter
  }
})
