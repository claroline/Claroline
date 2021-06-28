import {trans} from '#/main/app/intl/translation'
import {currency} from '#/main/app/intl/currency'
import {chain, number, inRange} from '#/main/app/data/types/validators'

import {CurrencyInput} from '#/main/app/data/types/currency/components/input'

/**
 * Currency definition.
 * Manages currency values.
 */
const dataType = {
  name: 'currency',
  meta: {
    icon: 'fa fa-fw fa fa-calculator',
    label: trans('currency', {}, 'data'),
    description: trans('currency_desc', {}, 'data')
  },

  /**
   * @param {number} raw
   * @param {object} options
   *
   * @return {string}
   */
  render: (raw, options) => raw || 0 === raw ? currency(raw, options.currency) : null,

  /**
   * Validates a currency value.
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
    input: CurrencyInput
  }
}

export {
  dataType
}
