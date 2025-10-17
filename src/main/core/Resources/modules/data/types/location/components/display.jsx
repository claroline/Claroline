import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {EntityDisplay} from '#/main/app/data/types/entity'

import {Location as LocationTypes} from '#/main/core/data/types/location/prop-types'
import {Address} from '#/main/app/components/address'

const LocationDisplay = (props) => {
  if (props.data && (!props.multiple || 1 === props.data.length)) {
    const location = props.multiple ? props.data[0] : props.data

    return (
      <Address
        name={location.name}
        {...get(location, 'address', {})}
      />
    )
  }

  return (
    <EntityDisplay
      placeholder={trans('no_location', {}, 'location')}
      {...props}
    />
  )
}

LocationDisplay.propTypes = {
  multiple: T.bool,
  data: T.oneOfType([
    T.shape(
      LocationTypes.propTypes
    ),
    T.arrayOf(T.shape(
      LocationTypes.propTypes
    ))
  ])
}

export {
  LocationDisplay
}
