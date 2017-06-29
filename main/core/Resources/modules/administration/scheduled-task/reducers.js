import cloneDeep from 'lodash/cloneDeep'
import {makeReducer, combineReducers} from '#/main/core/utilities/redux'
import {VIEW_MANAGEMENT} from './enums'
import {makeListReducer} from '#/main/core/layout/list/reducer'
import {reducer as paginationReducer} from '#/main/core/layout/pagination/reducer'

import {
  UPDATE_VIEW_MODE,
  TASKS_LOAD,
  TASK_ADD,
  TASK_FORM_RESET,
  TASK_FORM_LOAD,
  TASK_FORM_TYPE_UPDATE
} from './actions'

const initialState = {
  isCronConfigured: false,
  viewMode: VIEW_MANAGEMENT,
  tasks: {
    data: [],
    total: 0
  },
  taskForm: {
    id: null,
    type: null,
    scheduledDate: null,
    name: null,
    data: null
  }
}

const mainReducers = {}

const viewReducers = {
  [UPDATE_VIEW_MODE]: (state, action) => {
    return action.mode
  }
}

const tasksReducers = {
  [TASKS_LOAD]: (state, action) => {
    return {
      data: action.tasks,
      total: action.total
    }
  },
  [TASK_ADD]: (state, action) => {
    const tasks = cloneDeep(state.data)
    tasks.push(action.task)

    return {
      data: tasks,
      total: state.total + 1
    }
  }
}

const taskFormReducers = {
  [TASK_FORM_RESET]: () => initialState['taskForm'],
  [TASK_FORM_LOAD]: (state, action) => action.task,
  [TASK_FORM_TYPE_UPDATE]: (state, action) => Object.assign({}, state, {type: action.value})
}

export const reducers = combineReducers({
  isCronConfigured: makeReducer(initialState['isCronConfigured'], mainReducers),
  viewMode: makeReducer(initialState['viewMode'], viewReducers),
  tasks: makeReducer(initialState['tasks'], tasksReducers),
  taskForm: makeReducer(initialState['taskForm'], taskFormReducers),
  list: makeListReducer(),
  pagination: paginationReducer
})