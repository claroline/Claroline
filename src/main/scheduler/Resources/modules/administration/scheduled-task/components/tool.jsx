import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'
import {AlertBlock} from '#/main/app/alert/components/alert-block'

import {ToolPage} from '#/main/core/tool/containers/page'

import {ScheduledTaskList} from '#/main/scheduler/administration/scheduled-task/containers/list'
import {ScheduledTaskForm} from '#/main/scheduler/administration/scheduled-task/containers/form'

const ScheduledTaskTool = props =>
  <ToolPage
    primaryAction="add execute-all"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_scheduled_task', {}, 'scheduler'),
        target: `${props.path}/form`,
        primary: true,
        exact: true,
        disabled: !props.isSchedulerEnabled
      }, {
        name: 'execute-all',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-refresh',
        label: trans('execute_all', {}, 'actions'),
        callback: () => props.execute()
      }
    ]}
  >
    {!props.isSchedulerEnabled &&
      <AlertBlock type="warning" title={trans('cron_not_configured', {}, 'scheduler')} style={{marginTop: 20}}>
        {trans('cron_not_configured_help', {}, 'scheduler')}
      </AlertBlock>
    }

    <Routes
      path={props.path}
      routes={[
        {
          path: '/',
          exact: true,
          component: ScheduledTaskList
        }, {
          path: '/form/:id?',
          component: ScheduledTaskForm,
          onEnter: (params) => props.openForm(params.id || null)
        }
      ]}
    />
  </ToolPage>

ScheduledTaskTool.propTypes = {
  path: T.string.isRequired,
  isSchedulerEnabled: T.bool.isRequired,
  openForm: T.func.isRequired,
  execute: T.func.isRequired
}

export {
  ScheduledTaskTool
}
