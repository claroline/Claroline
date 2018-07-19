import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {
  PageContainer,
  PageActions,
  PageAction,
  PageHeader
} from '#/main/core/layout/page'
import {
  RoutedPageContent
} from '#/main/core/layout/router'

import {ScheduledTasks} from '#/main/core/administration/scheduled-task/components/scheduled-tasks'
import {ScheduledTask}  from '#/main/core/administration/scheduled-task/components/scheduled-task'
import {actions}        from '#/main/core/administration/scheduled-task/actions'
import {select}         from '#/main/core/administration/scheduled-task/selectors'

const Tool = props =>
  <PageContainer>
    <PageHeader
      title={trans('tasks_scheduling', {}, 'tools')}
    >
      <PageActions>
        <PageAction
          type="link"
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

Tool.propTypes = {
  isCronConfigured: T.bool.isRequired,
  openForm: T.func.isRequired
}

const ScheduledTaskTool = connect(
  state => ({
    isCronConfigured: select.isCronConfigured(state)
  }),
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open('task', id))
    }
  })
)(Tool)

export {
  ScheduledTaskTool
}
