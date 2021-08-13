import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/subscription/store/selectors'
import {UPDATE_SUBSCRIPTION_STATUS} from '#/plugin/cursus/tools/trainings/subscription/store/actions'

export const reducer = combineReducers({
  subscriptions: makeListReducer(selectors.LIST_NAME, false, {
    invalidated: makeReducer(false, {
      [UPDATE_SUBSCRIPTION_STATUS]: () => true
    })
  })
})
