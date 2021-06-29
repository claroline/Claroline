import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'

import {DataCard} from '#/main/app/data/components/card'
import {Room as RoomTypes} from '#/main/core/tools/locations/prop-types'

const RoomCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon="fa fa-door-open"
    title={props.data.name}
    subtitle={props.data.code}
    contentText={get(props.data, 'description')}
  />

RoomCard.propTypes = {
  data: T.shape(
    RoomTypes.propTypes
  ).isRequired
}

export {
  RoomCard
}
