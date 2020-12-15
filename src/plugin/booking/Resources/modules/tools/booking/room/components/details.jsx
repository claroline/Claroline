import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router/components/routes'
import {ContentTabs} from '#/main/app/content/components/tabs'
import {Sections, Section} from '#/main/app/content/components/sections'
import {ListData} from '#/main/app/content/list/containers/data'
import {DetailsData} from '#/main/app/content/details/containers/data'

import {Room as RoomTypes} from '#/plugin/booking/prop-types'
import {RoomPage} from '#/plugin/booking/tools/booking/room/containers/page'
import {selectors} from '#/plugin/booking/tools/booking/room/store/selectors'
import {MODAL_ROOM_BOOKING} from '#/plugin/booking/tools/booking/room/modals/booking'

const RoomAbout = (props) =>
  <DetailsData
    name={selectors.FORM_NAME}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'description',
            type: 'html',
            label: trans('description'),
            required: true
          }, {
            name: 'location',
            type: 'location',
            label: trans('location')
          }, {
            name: 'capacity',
            type: 'number',
            label: trans('capacity'),
            required: true,
            options: {
              min: 0
            }
          }
        ]
      }
    ]}
  >
    <Sections
      level={3}
    >
      <Section
        className="embedded-list-section"
        icon="fa fa-fw fa-calendar"
        title={trans('bookings', {}, 'booking')}
        actions={[
          {
            name: 'book',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('book', {}, 'actions'),
            modal: [MODAL_ROOM_BOOKING, {
              room: props.room,
              onSave: () => props.invalidateBookings()
            }]
          }
        ]}
      >
        <ListData
          name={`${selectors.FORM_NAME}.bookings`}
          fetch={{
            url: ['apiv2_booking_room_list_booking', {room: props.room.id}],
            autoload: true
          }}
          delete={{
            url: ['apiv2_booking_room_delete_booking', {room: props.room.id}]
          }}
          definition={[
            {
              name: 'dates[0]',
              alias: 'startDate',
              type: 'date',
              label: trans('start_date'),
              options: {time: true},
              displayed: true
            }, {
              name: 'dates[1]',
              alias: 'endDate',
              type: 'date',
              label: trans('end_date'),
              options: {time: true},
              displayed: true
            }, {
              name: 'description',
              type: 'string',
              label: trans('description'),
              options: {long: true},
              displayed: true
            }
          ]}
        />
      </Section>
    </Sections>
  </DetailsData>

RoomAbout.propTypes = {
  room: T.shape(
    RoomTypes.propTypes
  ),
  invalidateBookings: T.func.isRequired
}

const RoomDetails = (props) =>
  <RoomPage
    room={props.room}
  >
    <header className="row content-heading">
      <ContentTabs
        backAction={{
          type: LINK_BUTTON,
          target: `${props.path}/rooms`,
          exact: true
        }}
        sections={[
          {
            name: 'about',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-info',
            label: trans('about'),
            target: `${props.path}/rooms/${props.room.id}`,
            exact: true
          }, {
            name: 'planning',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-calendar',
            label: trans('planning', {}, 'booking'),
            target: `${props.path}/rooms/${props.room.id}/planning`,
            displayed: false
          }
        ]}
      />
    </header>

    <Routes
      path={`${props.path}/rooms/${props.room.id}`}
      routes={[
        {
          path: '/',
          exact: true,
          render: () => (
            <RoomAbout
              room={props.room}
              invalidateBookings={props.invalidateBookings}
            />
          )
        }
      ]}
    />
  </RoomPage>

RoomDetails.propTypes = {
  path: T.string.isRequired,
  room: T.shape(
    RoomTypes.propTypes
  ),
  invalidateBookings: T.func.isRequired
}

export {
  RoomDetails
}
