import {combineReducers, makeReducer} from '#/main/app/store/reducer'

// TODO : this should be moved in the the main app store when available

const reducer = {
  current: makeReducer(null),
  workspaces: combineReducers({
    personal: makeReducer(null),
    current: makeReducer(null),
    history: makeReducer([]),
    creatable: makeReducer(false)
  }),
  display: makeReducer({}),
  tools: makeReducer([]),
  userTools: makeReducer([]),
  administration: makeReducer([])
}

export {
  reducer
}
