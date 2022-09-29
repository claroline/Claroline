import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {PLUGIN_LOAD} from '#/main/core/administration/plugins/store/actions'
import {selectors} from '#/main/core/administration/plugins/store/selectors'

const reducer = combineReducers({
  plugin: makeReducer(null, {
    [PLUGIN_LOAD]: (state, action) => action.plugin
  }),
  plugins: makeListReducer(selectors.STORE_NAME+'.plugins')
})

export {
  reducer
}
