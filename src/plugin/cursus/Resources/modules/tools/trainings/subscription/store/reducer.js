import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/subscription/store/selectors'
import {UPDATE_SUBSCRIPTION_STATUS, SET_STATISTICS} from '#/plugin/cursus/tools/trainings/subscription/store/actions'

export const reducer = combineReducers({
  subscriptions: makeListReducer(selectors.LIST_NAME, false, {
    invalidated: makeReducer(false, {
      [UPDATE_SUBSCRIPTION_STATUS]: () => true
    })
  }),
  statistics: makeReducer({
    total: 0,
    pending: 0,
    refused: 0,
    validated: 0,
    managed: 0,
    calculated: 0
  }, {
    [SET_STATISTICS]: (state, action) => action.statistics
  })
})
