import {makeReducer} from '#/main/app/store/reducer'

import {COLOR_CHART_LOAD } from '#/main/theme/color/store/actions'

export const reducer = makeReducer({}, {
  [COLOR_CHART_LOAD]: (state, action) => action.colorChart
})
