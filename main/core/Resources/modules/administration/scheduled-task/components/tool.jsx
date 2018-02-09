import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {navigate, matchPath, withRouter} from '#/main/core/router'
import {
  PageActions,
  PageHeader
} from '#/main/core/layout/page'
import {
  RoutedPageContainer,
  RoutedPageContent
} from '#/main/core/layout/router'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {ScheduledTasks} from '#/main/core/administration/scheduled-task/components/scheduled-tasks.jsx'
import {ScheduledTask}  from '#/main/core/administration/scheduled-task/components/scheduled-task.jsx'
import {actions}        from '#/main/core/administration/scheduled-task/actions'
import {select}         from '#/main/core/administration/scheduled-task/selectors'

const ToolActions = props =>
  <PageActions>
    <FormPageActionsContainer
      formName="task"
      target={(task, isNew) => isNew ?
        ['apiv2_scheduledtask_create'] :
        ['apiv2_scheduledtask_update', {id: task.id}]
      }
      opened={!!matchPath(props.location.pathname, {path: '/form'})}
      open={{
        icon: 'fa fa-plus',
        disabled: !props.isCronConfigured,
        label: trans('add_scheduled_task'),
        action: '#/form'
      }}
      cancel={{
        action: () => navigate('/')
      }}
    />
  </PageActions>

ToolActions.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired,
  isCronConfigured: T.bool.isRequired
}

const ToolPageActions = withRouter(ToolActions)

const Tool = props =>
  <RoutedPageContainer>
    <PageHeader
      title={trans('tasks_scheduling', {}, 'tools')}
    >
      <ToolPageActions
        isCronConfigured={props.isCronConfigured}
      />
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
  </RoutedPageContainer>

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
