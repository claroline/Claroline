import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {
  PageContainer,
  PageActions,
  PageAction,
  PageHeader
} from '#/main/core/layout/page'
import {
  RoutedPageContent
} from '#/main/core/layout/router'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ScheduledTasks} from '#/main/core/administration/scheduled-task/components/scheduled-tasks'
import {ScheduledTask}  from '#/main/core/administration/scheduled-task/components/scheduled-task'

const ScheduledTaskTool = props =>
  <PageContainer>
    <PageHeader
      title={trans('tasks_scheduling', {}, 'tools')}
    >
      <PageActions>
        <PageAction
          type={LINK_BUTTON}
          icon="fa fa-plus"
          label={trans('add_scheduled_task')}
          disabled={!props.isCronConfigured}
          target="/form"
          exact={true}
          primary={true}
        />
      </PageActions>
    </PageHeader>

    <RoutedPageContent
      routes={[
        {
          path: '/',
          component: ScheduledTasks,
          exact: true
        }, {
          path: '/form/:id?',
          component: ScheduledTask,
          onEnter: (params) => props.openForm(params.id || null)
        }
      ]}
    />
  </PageContainer>

ScheduledTaskTool.propTypes = {
  isCronConfigured: T.bool.isRequired,
  openForm: T.func.isRequired
}

export {
  ScheduledTaskTool
}
