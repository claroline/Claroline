import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {param} from '#/main/app/config'

import {ScheduledTaskTool as ScheduledTaskToolComponent} from '#/main/scheduler/administration/scheduled-task/components/tool'
import {actions, selectors} from '#/main/scheduler/administration/scheduled-task/store'

const ScheduledTaskTool = withRouter(
  connect(
    () => ({
      isSchedulerEnabled: param('schedulerEnabled')
    }),
    dispatch => ({
      openForm(id = null) {
        dispatch(actions.open(selectors.STORE_NAME + '.task', id))
      },
      execute() {
        dispatch(actions.execute())
      }
    })
  )(ScheduledTaskToolComponent)
)

export {
  ScheduledTaskTool
}
