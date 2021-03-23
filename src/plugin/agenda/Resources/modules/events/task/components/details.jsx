import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DetailsData} from '#/main/app/content/details/components/data'

import {EventPage} from '#/plugin/agenda/event/containers/page'
import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {EventIcon} from '#/plugin/agenda/event/components/icon'

import {TaskMain} from '#/plugin/agenda/events/task/containers/main'

const TaskDetails = (props) =>
  <TaskMain eventId={props.event.id}>
    <EventPage
      event={props.task}
      reload={(event) => {
        props.reload(event)
        props.open(event.id)
      }}
    >
      <DetailsData
        data={props.task}
        meta={true}
        sections={[{
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
            }, {
              name: 'location',
              type: 'location',
              label: trans('location')
            }
          ]
        }]}
      />
    </EventPage>
  </TaskMain>

TaskDetails.propTypes = {
  // from agenda
  path: T.string.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired,

  // from store
  task: T.object,
  open: T.func.isRequired
}

export {
  TaskDetails
}
