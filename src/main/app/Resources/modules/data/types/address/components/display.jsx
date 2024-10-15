import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Address as AddressTypes} from '#/main/app/data/types/address/prop-types'
import {Address} from '#/main/app/components/address'

const AddressDisplay = (props) => {
  const filledAddressParts = Object.keys(props.data || {})
    .map((name) => props.data[name])
    .filter(addressPart => !isEmpty(addressPart))

  if (isEmpty(filledAddressParts)) {
    return (
      <span className="text-secondary d-block" role="presentation">{trans('empty_value')}</span>
    )
  }

  return (
    <address>
      <Address {...props.data} />
    </address>
  )
}

AddressDisplay.propTypes = {
  data: T.shape(
    AddressTypes.propTypes
  ).isRequired
}

export {
  AddressDisplay
}
