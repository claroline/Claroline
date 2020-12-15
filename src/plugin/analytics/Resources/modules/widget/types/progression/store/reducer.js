import {makeReducer} from '#/main/app/store/reducer'
import {WIDGET_PROGRESSION_LOAD_ITEMS} from '#/plugin/analytics/widget/types/progression/store/actions'

const reducer = makeReducer([], {
  [WIDGET_PROGRESSION_LOAD_ITEMS]: (state, action) => action.items
})

export {reducer}