import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {EventPage} from '#/plugin/cursus/event/components/page'
import {EventDetails} from '#/plugin/cursus/event/containers/details'

const EventsDetails = (props) =>
  <EventPage
    basePath={props.path}
    path={props.event ? [
      {
        type: LINK_BUTTON,
        label: props.event.name,
        target: props.path + '/' + props.event.id
      }
    ] : undefined}
    event={props.event}
    reload={props.reload}
  >
    <EventDetails
      path={props.path}
    />
  </EventPage>

EventsDetails.propTypes = {
  path: T.string.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ),
  reload: T.func.isRequired
}

export {
  EventsDetails
}
