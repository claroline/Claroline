import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/administration/template/store/selectors'

const reducer = combineReducers({
  templates: makeListReducer(selectors.STORE_NAME + '.templates', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.template']: () => true,
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  template: makeFormReducer(selectors.STORE_NAME + '.template'),
  locales:makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.locales || []
  }),
  defaultLocale:makeReducer(null, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.defaultLocale || null
  })
})

export {
  reducer
}