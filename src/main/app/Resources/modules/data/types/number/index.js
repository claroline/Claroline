import {trans} from '#/main/app/intl/translation'
import {chain, number, inRange} from '#/main/app/data/types/validators'

import {NumberInput} from '#/main/app/data/types/number/components/input'

/**
 * Number definition.
 * Manages numeric values.
 */
const dataType = {
  name: 'number',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa fa-calculator',
    label: trans('number', {}, 'data'),
    description: trans('number_desc', {}, 'data')
  },

  /**
   * The list of configuration fields.
   */
  configure: (options) => [
    {
      name: 'min',
      type: 'number',
      label: trans('min_value'),
      options: {
        max: options.max
      }
    }, {
      name: 'max',
      type: 'number',
      label: trans('max_value'),
      options: {
        min: options.min
      }
    }, {
      name: 'unit',
      type: 'string',
      label: trans('unit')
    }
  ],

  parse: parseFloat,

  /**
   * Displays a number value.
   * NB. trans typing to string permits to avoid React interpret 0 value as falsy and display nothing.
   *
   * @param {number} raw
   * @param {object} options
   *
   * @return {string}
   */
  render: (raw, options) => raw || 0 === raw ? raw + (options.unit ? ' ' + options.unit : '') : null,

  /**
   * Validates a number value.
   *
   * @param {*}      value   - the value to validate
   * @param {object} options - the current number options
   *
   * @return {string} - the first error message if any
   */
  validate: (value, options) => chain(value, options, [number, inRange]),

  /**
   * Custom components for numbers rendering.
   */
  components: {
    input: NumberInput
  }
}

export {
  dataType
}
