import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/data/components/card'

import {getAddressString} from '#/main/app/data/types/address/utils'
import {Location as LocationTypes} from '#/main/core/user/prop-types'

const LocationCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon="fa fa-map-marker-alt"
    title={props.data.name}
    subtitle={getAddressString(props.data.address, true)}
    contentText={props.data.meta.description}
  />

LocationCard.propTypes = {
  data: T.shape(
    LocationTypes.propTypes
  ).isRequired
}

export {
  LocationCard
}
