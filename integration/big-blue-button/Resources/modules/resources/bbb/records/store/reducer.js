import {makeReducer} from '#/main/app/store/reducer'

import {RECORDINGS_LOAD} from '#/integration/big-blue-button/resources/bbb/records/store/actions'

const reducer = makeReducer([], {
  [RECORDINGS_LOAD]: (state, action) => action.recordings
})

export {
  reducer
}
