import merge from 'lodash/merge'
import set from 'lodash/set'

import {makeReducer} from '#/main/app/store/reducer'
import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'

import {APP_CONFIG_UPDATE, APP_CONFIG_LOAD} from '#/main/app/config/store/actions'

const reducer = makeReducer({}, {
  [SECURITY_USER_CHANGE]: (state, action) => {
    if (action.config) {
      return merge({}, state, action.config)
    }

    return state
  },
  [APP_CONFIG_LOAD]: (state, action) => action.config || state,
  [APP_CONFIG_UPDATE]: (state, action) => {
    const newConfig = merge({}, state)

    set(newConfig, action.configKey, action.configValue)

    return newConfig
  }
})

export {
  reducer
}