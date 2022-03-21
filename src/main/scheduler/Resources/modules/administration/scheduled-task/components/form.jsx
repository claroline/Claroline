import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {UserList} from '#/main/core/user/components/list'

import {constants} from '#/main/scheduler/administration/scheduled-task/constants'
import {selectors} from '#/main/scheduler/administration/scheduled-task/store'
import {MODAL_USERS} from '#/main/core/modals/users'

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
            name: 'action',
            type: 'choice',
            label: trans('task'),
            required: true,
            disabled: !props.new,
            options: {
              condensed: true,
              choices: constants.TASK_ACTIONS
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
        disabled={!props.task.id || props.new}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_users'),
            modal: [MODAL_USERS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addUsers(props.task.id, selected.map(row => row.id))
              })
            }]
          }
        ]}
      >
        {props.task.id && !props.new &&
          <UserList
            name={selectors.STORE_NAME + '.task.users'}
            url={['apiv2_scheduled_task_list_users', {id: props.task.id}]}
            delete={{
              url: ['apiv2_scheduled_task_remove_users', {id: props.task.id}]
            }}
          />
        }
      </FormSection>
    </FormSections>
  </FormData>

ScheduledTaskForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  task: T.shape({
    id: T.string
  }).isRequired,
  addUsers: T.func.isRequired
}

export {
  ScheduledTaskForm
}
