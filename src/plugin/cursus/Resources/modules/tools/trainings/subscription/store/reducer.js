import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {constants} from '#/plugin/cursus/constants'
import {selectors} from '#/plugin/cursus/tools/trainings/subscription/store/selectors'
import {SET_STATISTICS} from '#/plugin/cursus/tools/trainings/subscription/store/actions'

export const reducer = combineReducers({
  subscriptions: makeListReducer(selectors.LIST_NAME, {
    filters: [
      {
        property: 'status',
        value: constants.SUBSCRIPTION_STATUS_PENDING
      }
    ],
    sortBy: {
      property: 'startDate',
      direction: 1
    }
  }, {
    invalidated: makeReducer(false, {
      [SET_STATISTICS]: () => true
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
