import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {TASK_LOAD, TASK_SET_LOADED} from '#/plugin/agenda/events/task/store/actions'

const reducer = combineReducers({
  loaded: makeReducer(null, {
    [TASK_SET_LOADED]: (state, action) => action.loaded
  }),
  task: makeReducer(null, {
    [TASK_LOAD]: (state, action) => action.task
  })
})

export {
  reducer
}
