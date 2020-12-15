import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {selectors} from '#/main/core/resource/modals/rights/store/selectors'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {SET_RIGHTS_RECURSIVE} from '#/main/core/resource/modals/rights/store/actions'

const reducer = combineReducers({
  form: makeFormReducer(selectors.FORM_NAME, false, {
    pendingChanges: makeReducer(false, {
      [SET_RIGHTS_RECURSIVE]: () => true
    })
  }),
  recursiveEnabled: makeReducer(false, {
    [SET_RIGHTS_RECURSIVE]: (state, action) => action.recursiveEnabled,
    ['FORM_RESET/' + selectors.FORM_NAME]: () => false
  })
})

export {
  reducer
}
