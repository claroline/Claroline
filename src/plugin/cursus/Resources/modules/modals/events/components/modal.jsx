import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {PickerModal} from '#/main/app/data/modals/picker/components/modal'

import {EventStatus} from '#/plugin/cursus/components/event-status'

const EventsModal = (props) =>
  <PickerModal
    {...props}
    name="trainingEventsPicker"
    definition={[
      {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        sortable: false,
        displayed: true,
        filterable: true,
        order: 1,
        options: {
          noEmpty: true,
          choices: {
            not_started: trans('session_not_started', {}, 'cursus'),
            in_progress: trans('session_in_progress', {}, 'cursus'),
            ended: trans('session_ended', {}, 'cursus'),
            not_ended: trans('session_not_ended', {}, 'cursus')
          }
        },
        render: (row) => <EventStatus startDate={get(row, 'start')} endDate={get(row, 'end')} />
      }, {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'code',
        type: 'string',
        label: trans('code'),
        displayed: false
      }, {
        name: 'location',
        type: 'location',
        label: trans('location'),
        placeholder: trans('online_session', {}, 'cursus'),
        displayed: true,
        options: {multiple: false}
      }, {
        name: 'start',
        alias: 'startDate',
        type: 'date',
        label: trans('start_date'),
        displayed: true,
        options: {
          time: true
        }
      }, {
        name: 'end',
        alias: 'endDate',
        type: 'date',
        label: trans('end_date'),
        options: {
          time: true
        },
        displayed: true
      }
    ]}
  />

EventsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  multiple: T.bool,
  // from modal
  fadeModal: T.func.isRequired
}

EventsModal.defaultProps = {
  url: ['apiv2_training_event_list'],
  title: trans('session_events', {}, 'cursus')
}

export {
  EventsModal
}
