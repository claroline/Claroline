import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {TaskMain as TaskMainComponent} from '#/plugin/agenda/events/task/components/main'
import {actions, reducer, selectors} from '#/plugin/agenda/events/task/store'

const TaskMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state)
    }),
    (dispatch) => ({
      open(eventId) {
        dispatch(actions.open(eventId))
      }
    })
  )(TaskMainComponent)
)

export {
  TaskMain
}
