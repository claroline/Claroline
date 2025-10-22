import {createSelector} from 'reselect'

import {makeActionCreator, makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

const STORE_NAME = 'privacy'

// actions
export const PRIVACY_PARAMETERS_LOAD = 'PRIVACY_PARAMETERS_LOAD'

export const actions = {}
actions.loadPrivacyParameters = makeActionCreator(PRIVACY_PARAMETERS_LOAD, 'parameters')

// selectors
const store = (state) => state[STORE_NAME]

const parameters = createSelector(
  [store],
  (store) => store.parameters
)

const selectors = {
  STORE_NAME,
  parameters
}

// reducer
const reducer = combineReducers({
  parameters: makeReducer({dpo: {}, tos: {}}, {
    [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.privacy,
    [PRIVACY_PARAMETERS_LOAD]: (state, action) => action.parameters
  })
})

export {
  reducer,
  selectors
}
