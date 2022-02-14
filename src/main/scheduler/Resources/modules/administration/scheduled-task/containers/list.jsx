import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions} from '#/main/scheduler/administration/scheduled-task/store'
import {ScheduledTaskList as ScheduledTaskListComponent} from '#/main/scheduler/administration/scheduled-task/components/list'

const ScheduledTaskList = connect(
  state => ({
    path: toolSelectors.path(state)
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
