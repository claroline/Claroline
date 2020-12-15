import {makeReducer} from '#/main/app/store/reducer'

import {LOAD_STATISTICS} from '#/plugin/exo/resources/quiz/statistics/store/actions'

const reducer = makeReducer({}, {
  [LOAD_STATISTICS]: (state, action) => action.stats
})

export {
  reducer
}
