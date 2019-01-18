import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {ScheduledTaskTool as ScheduledTaskToolComponent} from '#/main/core/administration/scheduled-task/components/tool'
import {actions}        from '#/main/core/administration/scheduled-task/actions'
import {select}         from '#/main/core/administration/scheduled-task/selectors'

const ScheduledTaskTool = withRouter(
  connect(
    state => ({
      isCronConfigured: select.isCronConfigured(state)
    }),
    dispatch => ({
      openForm(id = null) {
        dispatch(actions.open('task', id))
      }
    })
  )(ScheduledTaskToolComponent)
)

export {
  ScheduledTaskTool
}
