import {RESPONSE_RECEIVE} from '#/main/app/api/store'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {NOTIFICATIONS_LOAD} from '#/main/notification/store/actions'

export const reducer = combineReducers({
  count: makeReducer(0, {
    [RESPONSE_RECEIVE]: (state, action) => {
      if (action.response && action.response.headers) {
        const count = action.response.headers.get('Claroline-Notifications')

        if (count) {
          return parseInt(count)
        }
      }

      return state
    }
  }),
  list: makeReducer([], {
    [NOTIFICATIONS_LOAD]: (state, action) => action.notifications || state
  })
})
