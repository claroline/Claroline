import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {param} from '#/main/app/config'

import {ScheduledTaskTool as ScheduledTaskToolComponent} from '#/main/scheduler/administration/scheduled-task/components/tool'
import {actions, reducer, selectors} from '#/main/scheduler/administration/scheduled-task/store'

const ScheduledTaskTool = withReducer(selectors.STORE_NAME, reducer)(
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
