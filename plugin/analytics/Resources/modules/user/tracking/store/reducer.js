import {makeReducer} from '#/main/app/store/reducer'

import {TRACKINGS_INIT} from '#/main/core/user/tracking/store/actions'

const reducer = {
  user: makeReducer({}, {}),
  evaluations: makeReducer({}, {
    [TRACKINGS_INIT]: (state, action) => action.trackings
  })
}

export {
  reducer
}
