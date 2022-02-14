import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {UserList} from '#/main/core/administration/community/user/components/user-list'

import {constants} from '#/main/scheduler/administration/scheduled-task/constants'
import {actions, selectors} from '#/main/scheduler/administration/scheduled-task/store'

const ScheduledTaskForm = props =>
  <FormData
    level={2}
    name={selectors.STORE_NAME + '.task'}
    target={(task, isNew) => isNew ?
      ['apiv2_scheduled_task_create'] :
      ['apiv2_scheduled_task_update', {id: task.id}]
    }
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
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
            type: 'choice',
            label: trans('task'),
            required: true,
            disabled: !props.new,
            options: {
              condensed: true,
              choices: constants.TASK_TYPES
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-envelope-o',
        title: trans('message'),
        fields: [
          {
            name: 'data.object',
            type: 'string',
            label: trans('object')
          }, {
            name: 'data.content',
            type: 'html',
            label: trans('content'),
            required: true,
            options: {
              minRows: 5
            }
          }
        ]
      }
    ]}
  >
    <FormSections level={3}>
      <FormSection
        icon="fa fa-fw fa-user"
        className="embedded-list-section"
        title={trans('users')}
        disabled={props.new}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_users'),
            callback: () => props.pickUsers(props.task.id)
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.task.users'}
          fetch={{
            url: ['apiv2_scheduled_task_list_users', {id: props.task.id}],
            autoload: props.task.id && !props.new
          }}
          delete={{
            url: ['apiv2_scheduled_task_remove_users', {id: props.task.id}]
          }}
          primaryAction={UserList.open}
          definition={UserList.definition}
          card={UserList.card}
        />
      </FormSection>
    </FormSections>
  </FormData>

ScheduledTaskForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  task: T.shape({
    id: T.string
  }).isRequired,
  pickUsers: T.func.isRequired
}

export {
  ScheduledTaskForm
}
