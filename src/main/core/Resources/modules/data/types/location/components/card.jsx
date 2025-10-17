import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/app/data/components/card'

import {getAddressString} from '#/main/app/data/types/address/utils'
import {Location as LocationTypes} from '#/main/core/data/types/location/prop-types'

const LocationCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.poster}
    title={props.data.name}
    name={props.data.name}
    contentText={getAddressString(props.data.address)}
  />

LocationCard.propTypes = {
  data: T.shape(
    LocationTypes.propTypes
  ).isRequired
}

export {
  LocationCard
}
