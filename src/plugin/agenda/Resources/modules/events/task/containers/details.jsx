import {connect} from 'react-redux'

import {TaskDetails as TaskDetailsComponent} from '#/plugin/agenda/events/task/components/details'
import {selectors, actions} from '#/plugin/agenda/events/task/store'

const TaskDetails = connect(
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
)(TaskDetailsComponent)

export {
  TaskDetails
}
