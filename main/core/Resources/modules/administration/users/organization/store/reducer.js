import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import cloneDeep from 'lodash/cloneDeep'

import {FORM_SUBMIT_SUCCESS, FORM_RESET} from '#/main/app/content/form/store/actions'
import {UPDATE_LIMIT} from '#/main/core/administration/users/organization/store/actions'

import {selectors as baseSelectors} from '#/main/core/administration/users/store'

const reducer = combineReducers({
  picker: makeListReducer(baseSelectors.STORE_NAME+'.organizations.picker'),
  list: makeListReducer(baseSelectors.STORE_NAME+'.organizations.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+baseSelectors.STORE_NAME+'.organizations.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer(baseSelectors.STORE_NAME+'.organizations.current', {}, {
    data: makeReducer({
      limit: {
        enabled: false,
        users: -1
      }
    }, {
      [FORM_RESET + '/'+baseSelectors.STORE_NAME+'.organizations.current']: (state) => {
        const data = cloneDeep(state)
        data.limit.enable = data.limit.users > -1

        return data
      },
      [UPDATE_LIMIT]: (state, action) => {
        const orga = cloneDeep(state)

        orga.limit = action.enable ? {users: 1, enable: action.enable}: {users: -1, enable: action.enable}

        return orga
      }
    }),
    workspaces: makeListReducer(baseSelectors.STORE_NAME+'.organizations.current.workspaces'),
    users: makeListReducer(baseSelectors.STORE_NAME+'.organizations.current.users'),
    groups: makeListReducer(baseSelectors.STORE_NAME+'.organizations.current.groups'),
    managers: makeListReducer(baseSelectors.STORE_NAME+'.organizations.current.managers')
  })
})

export {
  reducer
}
