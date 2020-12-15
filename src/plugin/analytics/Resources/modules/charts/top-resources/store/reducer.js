import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {TOP_RESOURCES_LOAD} from '#/plugin/analytics/charts/top-resources/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [TOP_RESOURCES_LOAD]: () => true
  }),
  data: makeReducer([], {
    [TOP_RESOURCES_LOAD]: (state, action) => action.data
  })
})
