import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text'
import {Address as AddressTypes} from '#/main/app/data/types/address/prop-types'

// TODO add copy button

const AddressDisplay = (props) => {
  const filledAddressParts = Object.keys(props.data)
    .map((name) => props.data[name])
    .filter(addressPart => !isEmpty(addressPart))

  if (isEmpty(filledAddressParts)) {
    return (
      <span className="data-details-empty">{trans('empty_value')}</span>
    )
  }

  return (
    <div className="address-display">
      {Object.keys(props.data)
        .map((name) => props.data[name])
        .filter(addressPart => !isEmpty(addressPart))
        .map(addressPart =>
          <div key={toKey(addressPart)}>{addressPart}</div>
        )
      }
    </div>
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
