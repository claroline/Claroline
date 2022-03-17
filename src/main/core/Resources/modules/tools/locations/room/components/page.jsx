import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Room as RoomTypes} from '#/main/core/tools/locations/prop-types'
import {MODAL_ROOM_BOOKING} from '#/main/core/tools/locations/room/modals/booking'
import {MODAL_ROOM_PARAMETERS} from '#/main/core/tools/locations/room/modals/parameters'

const RoomPage = (props) => {
  if (isEmpty(props.room)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('room_loading', {}, 'location')}
      />
    )
  }

  return (
    <ToolPage
      path={[
        {
          type: LINK_BUTTON,
          label: trans('rooms', {}, 'location'),
          target: `${props.path}/rooms`
        }, {
          type: LINK_BUTTON,
          label: get(props.room, 'name'),
          target: `${props.path}/rooms/${get(props.room, 'id')}`,
          displayed: !!props.room
        }
      ]}
      poster={get(props.room, 'poster.url')}
      title={get(props.room, 'name') || trans('locations', {}, 'tools')}
      subtitle={get(props.room, 'code') || trans('rooms', {}, 'location')}
      primaryAction="book"
      actions={[
        {
          name: 'book',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-calendar-plus',
          label: trans('book', {}, 'actions'),
          modal: [MODAL_ROOM_BOOKING, {
            room: props.room,
            onSave: () => props.invalidateBookings()
          }],
          primary: true
        }, {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_ROOM_PARAMETERS, {
            room: props.room,
            onSave: () => true // TODO : reload
          }],
          displayed: props.editable,
          group: trans('management')
        }
      ]}
    >
      {props.children}
    </ToolPage>
  )
}

RoomPage.propTypes = {
  path: T.string.isRequired,
  room: T.shape(
    RoomTypes.propTypes
  ),
  editable: T.bool.isRequired,
  bookable: T.bool.isRequired,
  invalidateBookings: T.func.isRequired,
  children: T.node
}

export {
  RoomPage
}
