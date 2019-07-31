import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {PLUGINS_LOAD} from '#/main/core/administration/parameters/main/store/actions'
import {selectors} from '#/main/core/administration/parameters/main/store/selectors'

const reducer = combineReducers({
  parameters: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'main_settings')]: (state, action) => action.toolData.parameters
    }),
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'main_settings')]: (state, action) => action.toolData.parameters
    })
  }),
  availableLocales: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'main_settings')]: (state, action) => action.toolData.availableLocales
  }),
  plugins: makeReducer([], {
    [PLUGINS_LOAD]: (state, action) => action.plugins
  }),
  messages: combineReducers({
    list: makeListReducer(selectors.STORE_NAME+'.messages.list', {}, {
      invalidated: makeReducer(false, {
        [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.messages.current']: () => true
      })
    }),
    current: makeFormReducer(selectors.STORE_NAME+'.messages.current')
  })
})

export {
  reducer
}
