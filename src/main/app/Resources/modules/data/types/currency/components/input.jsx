import React from 'react'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {NumberInput} from '#/main/app/data/types/number/components/input'

const CurrencyInput = (props) =>
  <NumberInput
    {...omit(props, 'currency')}
    unit={trans(`currency.${props.currency || param('pricing.currency')}_short`)}
  />

implementPropTypes(CurrencyInput, DataInputTypes, {
  // more precise value type
  value: T.number,
  currency: T.string
}, {
  value: ''
})

export {
  CurrencyInput
}
