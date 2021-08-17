import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer, FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/quota/store/selectors'
import {LOAD_QUOTA} from '#/plugin/cursus/tools/trainings/quota/store/actions'
import {reducer as subscriptionReducer} from '#/plugin/cursus/tools/trainings/subscription/store/reducer'

export const reducer = combineReducers({
  quotas: makeListReducer(selectors.LIST_NAME, {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.FORM_NAME]: () => true
    })
  }),
  quotaForm: makeFormReducer(selectors.FORM_NAME),
  quota: makeReducer(null, {
    [LOAD_QUOTA]: (state, action) => action.quota
  }),
  subscription: subscriptionReducer
})
