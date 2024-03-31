import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {ScheduledTaskList} from '#/main/scheduler/administration/scheduled-task/containers/list'
import {ScheduledTaskForm} from '#/main/scheduler/administration/scheduled-task/containers/form'

const ScheduledTaskTool = props =>
  <Tool
    {...props}
    pages={[
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

ScheduledTaskTool.propTypes = {
  openForm: T.func.isRequired
}

export {
  ScheduledTaskTool
}
