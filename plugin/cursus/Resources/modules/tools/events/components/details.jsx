import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
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
        label: trans('training_events', {}, 'cursus'),
        target: props.path
      }, {
        type: LINK_BUTTON,
        label: props.event.name,
        target: props.path + '/' + props.event.id
      }
    ] : undefined}
    currentContext={props.currentContext}
    event={props.event}
  >
    <EventDetails
      path={props.path}
    />
  </EventPage>

EventsDetails.propTypes = {
  path: T.string.isRequired,
  currentContext: T.shape({
    type: T.oneOf(['administration', 'desktop', 'workspace']),
    data: T.object
  }).isRequired,
  event: T.shape(
    EventTypes.propTypes
  )
}

export {
  EventsDetails
}
