import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/app/content/card/components/data'

import {locationTypes} from '#/main/core/administration/community/location/constants'
import {Location as LocationTypes} from '#/main/core/user/prop-types'

// todo display address
// todo display coords

const LocationCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-location-arrow"
    title={props.data.name}
    subtitle={locationTypes[props.data.type]}
  />

LocationCard.propTypes = {
  data: T.shape(
    LocationTypes.propTypes
  ).isRequired
}

export {
  LocationCard
}
