import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {EventAbout as BaseEventAbout} from '#/plugin/agenda/event/containers/about'
import {EventMain} from '#/plugin/agenda/events/event/containers/main'

const EventAbout = (props) =>
  <EventMain eventId={props.event.id}>
    {props.agendaEvent &&
      <BaseEventAbout
        event={props.agendaEvent}
        sections={[
          {
            name: 'url',
            type: 'url',
            label: trans('url', {}, 'data'),
            calculated: (event) => {
              if (event.workspace) {
                return `${url(['claro_index', {}, true])}#${workspaceRoute(event.workspace, 'agenda')}/event/${event.id}`
              }

              return `${url(['claro_index', {}, true])}#${route('agenda')}/event/${event.id}`
            }
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
          }, {
            name: 'location',
            type: 'location',
            label: trans('location')
          }
        ]}
        actions={[
          {
            name: 'send-invitations',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-paper-plane',
            label: trans('send-invitations', {}, 'actions'),
            callback: () => props.sendInvitations(props.agendaEvent.id),
            displayed: hasPermission('edit', props.agendaEvent) && false
          }
        ]}
        reload={props.reload}
      />
    }
  </EventMain>

EventAbout.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ),
  reload: T.func.isRequired,

  agendaEvent: T.object,
  open: T.func.isRequired,
  sendInvitations: T.func.isRequired
}

export {
  EventAbout
}
