import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {constants} from '#/main/scheduler/administration/scheduled-task/constants'
import {selectors} from '#/main/scheduler/administration/scheduled-task/store'
import {ScheduledTaskCard} from '#/main/scheduler/administration/scheduled-task/components/card'

const ScheduledTaskList = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.tasks'}
    fetch={{
      url: ['apiv2_scheduled_task_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/form/${row.id}`
    })}
    delete={{
      url: ['apiv2_scheduled_task_delete_bulk'],
      disabled: (rows) => -1 === rows.findIndex(row => hasPermission('delete', row))
    }}
    definition={[
      {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        displayed: true,
        options: {
          noEmpty: true,
          choices: constants.TASK_STATUSES
        },
        render: (row) => (
          <span className={classes('label', {
            'label-default': constants.TASK_STATUS_PENDING === row.status,
            'label-success': constants.TASK_STATUS_SUCCESS === row.status,
            'label-info': constants.TASK_STATUS_IN_PROGRESS === row.status,
            'label-danger': constants.TASK_STATUS_ERROR === row.status
          })}>
            {trans('task_'+row.status, {}, 'scheduler')}
          </span>
        )
      }, {
        name: 'name',
        label: trans('name'),
        type: 'string',
        primary: true,
        displayed: true
      }, {
        name: 'action',
        label: trans('task'),
        type: 'translation',
        displayed: true
      }, {
        name: 'executionType',
        label: trans('type'),
        type: 'choice',
        options: {
          choices: constants.TASK_TYPES
        },
        displayed: true
      }, {
        name: 'scheduledDate',
        type: 'date',
        label: trans('scheduled_date', {}, 'scheduler'),
        displayed: true,
        options: {
          time: true
        }
      }, {
        name: 'executionDate',
        type: 'date',
        label: trans('execution_date', {}, 'scheduler'),
        displayed: true,
        options: {
          time: true
        }
      }
    ]}
    actions={(rows) => [
      {
        name: 'edit',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        target: `${props.path}/form/${rows[0].id}`,
        scope: ['object'],
        group: trans('management')
      }, {
        name: 'execute',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-refresh',
        label: trans('execute', {}, 'actions'),
        callback: () => props.execute(rows.map(row => row.id))
      }
    ]}
    card={ScheduledTaskCard}
  />

ScheduledTaskList.propTypes = {
  path: T.string.isRequired,
  execute: T.func.isRequired
}

export {
  ScheduledTaskList
}
