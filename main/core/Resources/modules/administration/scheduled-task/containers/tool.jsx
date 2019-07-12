import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {ScheduledTaskTool as ScheduledTaskToolComponent} from '#/main/core/administration/scheduled-task/components/tool'
import {actions, selectors} from '#/main/core/administration/scheduled-task/store'

const ScheduledTaskTool = withRouter(
  connect(
    state => ({
      isCronConfigured: selectors.isCronConfigured(state)
    }),
    dispatch => ({
      openForm(id = null) {
        dispatch(actions.open(selectors.STORE_NAME + '.task', id))
      }
    })
  )(ScheduledTaskToolComponent)
)

export {
  ScheduledTaskTool
}
