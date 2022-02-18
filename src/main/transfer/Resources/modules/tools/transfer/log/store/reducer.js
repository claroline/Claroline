import {makeReducer} from '#/main/app/store/reducer'

import {
  LOG_REFRESH,
  LOG_RESET
} from '#/main/transfer/tools/transfer/log/store/actions'

const reducer = makeReducer({}, {
  [LOG_REFRESH]: (state, action) => {
    try {
      return JSON.parse(action.content)
    } catch (e) {
      return {}
    }
  },
  [LOG_RESET]: () => ({})
})

export {
  reducer
}
