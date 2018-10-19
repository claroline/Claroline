import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {LOG_REFRESH} from '#/main/core/workspace/creation/store/actions'

const reducer = combineReducers({
  log: makeReducer({}, {
    [LOG_REFRESH]: (state, action) => {
      try {
        return JSON.parse(action.content)
      } catch (e) {
        return {}
      }
    }
  })
})

export {
  reducer
}
