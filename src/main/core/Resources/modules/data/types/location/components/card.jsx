import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {route} from '#/main/core/tool/routing'

import {asset} from '#/main/app/config/asset'
import {LINK_BUTTON} from '#/main/app/buttons'
import {DataCard} from '#/main/app/data/components/card'

import {getAddressString} from '#/main/app/data/types/address/utils'
import {Location as LocationTypes} from '#/main/community/prop-types'

const LocationCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    icon="fa fa-map-marker-alt"
    title={props.data.name}
    subtitle={getAddressString(props.data.address, true)}
    contentText={get(props.data, 'meta.description')}
    primaryAction={{
      type: LINK_BUTTON,
      target: route('locations') + '/locations/' + props.data.id
    }}
  />

LocationCard.propTypes = {
  data: T.shape(
    LocationTypes.propTypes
  ).isRequired
}

export {
  LocationCard
}
