import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {LOAD_PLATFORM_ABOUT} from '#/main/app/layout/sections/administration/modals/about/store/actions'

const reducer = combineReducers({
  version: makeReducer(null, { // we might retrieve it from global ui config
    [LOAD_PLATFORM_ABOUT]: (state, action) => action.version
  }),
  changelogs: makeReducer(null, {
    [LOAD_PLATFORM_ABOUT]: (state, action) => action.changelogs
  })
})

export {
  reducer
}
