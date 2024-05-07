import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {FORM_SUBMIT_SUCCESS, makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/plugin/link/resources/shortcut/store/selectors'

const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'shortcut')]: (state, action) => action.resourceData.resource || state,
  })
})

export {
  reducer
}
