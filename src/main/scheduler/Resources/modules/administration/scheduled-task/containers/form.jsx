import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ScheduledTaskForm as ScheduledTaskFormComponent} from '#/main/scheduler/administration/scheduled-task/components/form'
import {actions, selectors} from '#/main/scheduler/administration/scheduled-task/store'

const ScheduledTaskForm = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.task')),
    task: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.task'))
  }),
  dispatch =>({
    addUsers(taskId, selected) {
      dispatch(actions.addUsers(taskId, selected))
    }
  })
)(ScheduledTaskFormComponent)

export {
  ScheduledTaskForm
}
