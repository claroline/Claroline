import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'

import {ScheduledTasks} from '#/main/core/administration/scheduled-task/components/scheduled-tasks'
import {ScheduledTask} from '#/main/core/administration/scheduled-task/components/scheduled-task'

const ScheduledTaskTool = props =>
  <ToolPage
    actions={[
      {
        name: 'new_scheduled_task',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_scheduled_task'),
        target: `${props.path}/form`,
        primary: true,
        displayed: props.isCronConfigured
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/',
          render: () => {
            const component = <ScheduledTasks path={props.path} />

            return component
          },
          exact: true
        }, {
          path: '/form/:id?',
          component: ScheduledTask,
          onEnter: (params) => props.openForm(params.id || null)
        }
      ]}
    />
  </ToolPage>

ScheduledTaskTool.propTypes = {
  path: T.string.isRequired,
  isCronConfigured: T.bool.isRequired,
  openForm: T.func.isRequired
}

export {
  ScheduledTaskTool
}
