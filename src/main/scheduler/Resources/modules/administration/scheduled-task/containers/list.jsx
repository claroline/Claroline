import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as configSelectors} from '#/main/app/config/store'

import {actions} from '#/main/scheduler/administration/scheduled-task/store'
import {ScheduledTaskList as ScheduledTaskListComponent} from '#/main/scheduler/administration/scheduled-task/components/list'
import {param} from '#/main/app/config'

const ScheduledTaskList = connect(
  state => ({
    path: toolSelectors.path(state),
    isSchedulerEnabled: configSelectors.param(state, 'schedulerEnabled')
  }),
  (dispatch) => ({
    execute(tasks) {
      dispatch(actions.execute(tasks))
    }
  })
)(ScheduledTaskListComponent)

export {
  ScheduledTaskList
}
