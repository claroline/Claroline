import {makeReducer} from '#/main/core/scaffolding/reducer'

import {
  LOG_REFRESH,
  LOG_RESET
} from './actions'

const reducer = makeReducer({}, {
  [LOG_REFRESH]: (state, action) => {
    try {
      return JSON.parse(action.content)
    } catch (e) {
      return {}
    }
  },
  [LOG_RESET]: () => {return {}}
})

export {
  reducer
}
