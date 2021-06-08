import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {RoomCard} from '#/main/core/data/types/room/components/card'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const RoomDisplay = (props) => props.data ?
  <RoomCard
    data={props.data}
    size="xs"
  /> :
  <ContentPlaceholder
    icon="fa fa-door-open"
    title={trans('no_room')}
  />

RoomDisplay.propTypes = {
  data: T.object
}

export {
  RoomDisplay
}
