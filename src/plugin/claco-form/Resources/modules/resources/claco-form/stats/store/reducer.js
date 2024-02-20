import {makeReducer} from '#/main/app/store/reducer'

import {LOAD_CLACOFORM_STATS} from '#/plugin/claco-form/resources/claco-form/stats/store/actions'

const reducer = makeReducer(null, {
  [LOAD_CLACOFORM_STATS]: (state, action) => action.stats
})

export {
  reducer
}
