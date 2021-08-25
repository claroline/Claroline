import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security/permissions'
import {URL_BUTTON} from '#/main/app/buttons'
import {DetailsData} from '#/main/app/content/details/components/data'

import {EventPage} from '#/plugin/agenda/event/containers/page'
import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {EventIcon} from '#/plugin/agenda/event/components/icon'

import {EventMain} from '#/plugin/agenda/events/event/containers/main'
import {EventParticipants} from '#/plugin/agenda/events/event/containers/participants'

const EventDetails = (props) =>
  <EventMain eventId={props.event.id}>
    <EventPage
      event={props.agendaEvent}
      reload={(event) => {
        props.reload(event)
        props.open(event.id)
      }}
      actions={props.agendaEvent ? [
        {
          name: 'export-ics',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-calendar',
          label: trans('export-ics', {}, 'actions'),
          group: trans('transfer'),
          target: ['apiv2_event_download_ics', {id: props.agendaEvent.id}]
        }
      ] : []}
    >
      <DetailsData
        data={props.agendaEvent}
        meta={true}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'meta.type',
                type: 'type',
                label: trans('type'),
                hideLabel: true,
                calculated: (event) => ({
                  icon: <EventIcon type={event.meta.type} />,
                  name: trans(event.meta.type, {}, 'event'),
                  description: trans(`${event.meta.type}_desc`, {}, 'event')
                })
              }, {
                name: 'dates',
                type: 'date-range',
                label: trans('date'),
                calculated: (event) => [event.start || null, event.end || null],
                options: {
                  time: true
                }
              }, {
                name: 'description',
                type: 'html',
                label: trans('description')
              }
            ]
          }, {
            icon: 'fa fa-fw fa-map-marker-alt',
            title: trans('location'),
            fields: [
              {
                name: '_locationType',
                type: 'choice',
                label: trans('type'),
                hideLabel: true,
                calculated: (event) => {
                  if (event.location) {
                    return 'irl'
                  }

                  return 'online'
                },
                options: {
                  choices: {
                    online: trans('online'),
                    irl: trans('irl')
                  }
                },
                linked: [
                  {
                    name: 'locationUrl',
                    label: trans('url'),
                    type: 'url',
                    displayed: (event) => !isEmpty(event.locationUrl)
                  }, {
                    name: 'location',
                    label: trans('location'),
                    type: 'location',
                    displayed: (event) => !isEmpty(event.location)
                  }, {
                    name: 'room',
                    label: trans('room'),
                    type: 'room',
                    displayed: (event) => !isEmpty(event.location)
                  }
                ]
              }
            ]
          }
        ]}
      >
        <EventParticipants
          isNew={false}
          eventId={props.event.id}
          canEdit={!!props.agendaEvent && hasPermission('edit', props.agendaEvent)}
        />
      </DetailsData>
    </EventPage>
  </EventMain>

EventDetails.propTypes = {
  // from agenda
  path: T.string.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired,

  // from store
  agendaEvent: T.object,
  open: T.func.isRequired
}

export {
  EventDetails
}
