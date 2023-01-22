import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/competency/tools/evaluation/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME + '.scales.list', {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.scales.current']: () => true
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.scales.current', {}, {})
})

export {
  reducer
}