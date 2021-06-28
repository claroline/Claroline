import {connect} from 'react-redux'

import {TaskAbout as TaskAboutComponent} from '#/plugin/agenda/events/task/components/about'
import {actions, selectors} from '#/plugin/agenda/events/task/store'

const TaskAbout =
  connect(
    (state) => ({
      task: selectors.task(state)
    }),
    (dispatch) => ({
      open(eventId) {
        dispatch(actions.open(eventId))
      },
      markDone(taskId) {
        dispatch(actions.markDone(taskId))
      },
      markTodo(taskId) {
        dispatch(actions.markTodo(taskId))
      }
    })
  )(TaskAboutComponent)

export {
  TaskAbout
}
