import {param} from '#/main/app/config'
import {trans} from '#/main/app/intl/translation'
import {precision} from '#/main/app/intl/number'

function currency(value, unit = param('pricing.currency'), short = true) {
  return trans('currency.value', {value: precision(value, 2), unit: trans(`currency.${unit}${short ? '_short' : ''}`)})
}

export {
  currency
}
