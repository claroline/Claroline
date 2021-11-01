import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/administration/template/store/selectors'
import {TEMPLATE_TYPE_LOAD} from '#/main/core/administration/template/store/actions'

const reducer = combineReducers({
  current: makeReducer(null, {
    [TEMPLATE_TYPE_LOAD]: (state, action) => action.templateType
  }),
  templates: makeListReducer(selectors.STORE_NAME + '.templates', {
    //sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.template']: () => true,
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  template: makeFormReducer(selectors.STORE_NAME + '.template')
})

export {
  reducer
}
