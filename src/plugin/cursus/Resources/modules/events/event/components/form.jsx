import React from 'react'
import {PropTypes as T} from 'prop-types'

import {EventForm as BaseEventForm} from '#/plugin/agenda/event/containers/form'
import {EventForm as TrainingEventForm} from '#/plugin/cursus/event/components/form'

const EventForm = (props) =>
  <BaseEventForm
    name={props.name}
    target={(event, isNew) => isNew ? ['apiv2_cursus_event_create'] : ['apiv2_cursus_event_update', {id: event.id}]}
    onSave={props.onSave}
  >
    <TrainingEventForm
      name={props.name}
      embedded={true}
      update={props.update}
    />
  </BaseEventForm>

EventForm.propTypes = {
  name: T.string.isRequired,
  update: T.func.isRequired,
  onSave: T.func
}

export {
  EventForm
}
