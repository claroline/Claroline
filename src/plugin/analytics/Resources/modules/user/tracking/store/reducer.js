import {makeReducer} from '#/main/app/store/reducer'

import {TRACKING_INIT} from '#/plugin/analytics/user/tracking/store/actions'

const reducer = makeReducer([], {
  [TRACKING_INIT]: (state, action) => action.tracking || []
})

export {
  reducer
}
