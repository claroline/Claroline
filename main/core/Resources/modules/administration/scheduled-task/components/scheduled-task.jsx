import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {select as formSelect} from '#/main/core/data/form/selectors'

import {constants} from '#/main/core/administration/scheduled-task/constants'

const ScheduledTaskForm = props =>
  <FormContainer
    level={2}
    name="task"
    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'scheduledDate',
            type: 'date',
            label: trans('planning_date'),
            required: true,
            options: {
              time: true
            }
          }, {
            name: 'type',
            type: 'enum',
            label: trans('task'),
            required: true,
            disabled: !props.new,
            options: {
              choices: constants.TASK_TYPES
            }
          }
        ]
      }, {
        id: 'message',
        icon: 'fa fa-fw fa-envelope-o',
        title: trans('message'),
        fields: [
          {
            name: 'users',
            type: 'users',
            required: true,
            label: trans('receivers')
          }, {
            name: 'data.object',
            type: 'string',
            label: trans('message_form_object')
          }, {
            name: 'data.content',
            type: 'html',
            label: trans('message_form_content'),
            required: true,
            options: {
              minRows: 5
            }
          }
        ]
      }
    ]}
  />

ScheduledTaskForm.propTypes = {
  new: T.bool.isRequired
}

const ScheduledTask = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'task'))
  })
)(ScheduledTaskForm)

export {
  ScheduledTask
}
