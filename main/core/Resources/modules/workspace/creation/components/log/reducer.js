import {makeReducer} from '#/main/core/scaffolding/reducer'

import {
  LOG_REFRESH
} from './actions'

const reducer = makeReducer({}, {
  [LOG_REFRESH]: (state, action) => {
    try {
      return JSON.parse(action.content)
    } catch (e) {
      return {}
    }
  }
})

export {
  reducer
}
