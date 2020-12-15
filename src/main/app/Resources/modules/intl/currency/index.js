import {trans} from '#/main/app/intl/translation'
import {precision} from '#/main/app/intl/number'

function currency(value, unit, short = true) {
  return trans('currency.value', {value: precision(value, 2), unit: trans(`currency.${unit}${short ? '_short' : ''}`)})
}

export {
  currency
}
