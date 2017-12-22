import {makeReducer, combineReducers} from '#/main/core/utilities/redux'
import {CHANGE_ORGANIZATION} from './actions'
import cloneDeep from 'lodash/cloneDeep'

const handlers = {
  [CHANGE_ORGANIZATION]: (state, action) => {
    state = cloneDeep(state)
    const idx = state.selected.findIndex(select => select.id === action.organization.id)
    idx >= 0 ? state.selected.splice(idx, 1) : state.selected.push(action.organization)

    return state
  }
}

const reducer = combineReducers({
  organizations: makeReducer({}, {}),
  options: makeReducer({}, handlers)
})

export {
  reducer
}
