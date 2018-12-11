import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {PLUGINS_LOAD} from '#/main/core/administration/parameters/main/store/actions'
import {selectors} from '#/main/core/administration/parameters/main/store/selectors'

const reducer = {
  [selectors.FORM_NAME]: makeFormReducer(selectors.FORM_NAME),
  availableLocales: makeReducer([]),
  plugins: makeReducer([], {
    [PLUGINS_LOAD]: (state, action) => action.plugins
  })
}

export {
  reducer
}
