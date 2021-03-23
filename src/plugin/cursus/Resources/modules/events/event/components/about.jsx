import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {Event as BaseEventTypes} from '#/plugin/agenda/prop-types'
import {EventAbout as BaseEventAbout} from '#/plugin/agenda/event/containers/about'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {EventMain} from '#/plugin/cursus/events/event/containers/main'

const EventAbout = (props) =>
  <EventMain eventId={props.event.id}>
    <BaseEventAbout
      event={props.trainingEvent}
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
        }, {
          name: 'code',
          label: trans('code'),
          type: 'string'
        }, {
          name: 'session',
          label: trans('session', {}, 'cursus'),
          type: 'training_session'
        }
      ]}
      reload={props.reload}
    />
  </EventMain>

EventAbout.propTypes = {
  // from agenda
  event: T.shape(
    BaseEventTypes.propTypes
  ),
  reload: T.func.isRequired,

  // from store
  trainingEvent: T.shape(
    EventTypes.propTypes
  )
}

export {
  EventAbout
}
