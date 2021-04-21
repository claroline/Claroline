import React from 'react'
import {PropTypes as T} from 'prop-types'

import {EventPage} from '#/plugin/agenda/event/containers/page'
import {Event as BaseEventTypes} from '#/plugin/agenda/prop-types'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {EventMain} from '#/plugin/cursus/events/event/containers/main'
import {EventDetails as TrainingEventDetails} from '#/plugin/cursus/event/containers/details'

const EventDetails = (props) =>
  <EventMain eventId={props.event.id}>
    <EventPage
      event={props.trainingEvent}
      reload={(event) => {
        props.reload(event)
        props.open(event.id, true)
      }}
    >
      <TrainingEventDetails path={props.path} />
    </EventPage>
  </EventMain>

EventDetails.propTypes = {
  // from agenda
  path: T.string.isRequired,
  event: T.shape(
    BaseEventTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired,

  // from store
  trainingEvent: T.shape(
    EventTypes.propTypes
  ),
  open: T.func.isRequired
}

export {
  EventDetails
}
