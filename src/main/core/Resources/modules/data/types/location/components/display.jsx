import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {Location as LocationType} from '#/main/core/user/prop-types'
import {LocationCard} from '#/main/core/user/data/components/location-card'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const LocationDisplay = (props) => props.data ?
  <LocationCard
    data={props.data}
    size="xs"
  /> :
  <ContentPlaceholder
    icon="fa fa-map-marker-alt"
    title={trans('no_location')}
  />

LocationDisplay.propTypes = {
  data: T.shape(LocationType.propTypes)
}

export {
  LocationDisplay
}
