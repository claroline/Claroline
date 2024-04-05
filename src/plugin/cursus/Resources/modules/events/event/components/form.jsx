import React from 'react'
import {PropTypes as T} from 'prop-types'

import {EventForm as BaseEventForm} from '#/plugin/agenda/event/containers/form'
import {EventForm as TrainingEventForm} from '#/plugin/cursus/event/components/form'

const EventForm = (props) =>
  <BaseEventForm
    flush={props.flush}
    name={props.name}
    target={(event, isNew) => isNew ? ['apiv2_cursus_event_create'] : ['apiv2_cursus_event_update', {id: event.id}]}
    onSave={props.onSave}
  >
    <TrainingEventForm
      flush={props.flush}
      name={props.name}
      embedded={true}
      update={props.update}
      event={props.event}
    />
  </BaseEventForm>

EventForm.propTypes = {
  flush: T.bool,
  name: T.string.isRequired,
  event: T.object,
  update: T.func.isRequired,
  onSave: T.func
}

export {
  EventForm
}
