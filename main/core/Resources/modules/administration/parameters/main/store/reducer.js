import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {PLUGINS_LOAD} from '#/main/core/administration/parameters/main/store/actions'
import {selectors} from '#/main/core/administration/parameters/main/store/selectors'

const reducer = {
  [selectors.FORM_NAME]: makeFormReducer(selectors.FORM_NAME),
  availableLocales: makeReducer([]),
  plugins: makeReducer([], {
    [PLUGINS_LOAD]: (state, action) => action.plugins
  }),
  messages: combineReducers({
    list: makeListReducer('messages.list', {}, {
      invalidated: makeReducer(false, {
        [FORM_SUBMIT_SUCCESS+'/messages.current']: () => true
      })
    }),
    current: makeFormReducer('messages.current')
  })
}

export {
  reducer
}
