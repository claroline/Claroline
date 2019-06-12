import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import cloneDeep from 'lodash/cloneDeep'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {UPDATE_LIMIT} from '#/main/core/administration/user/organization/actions'
import {FORM_RESET} from '#/main/app/content/form/store/actions'

const reducer = combineReducers({
  picker: makeListReducer('organizations.picker'),
  list: makeListReducer('organizations.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/organizations.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer('organizations.current', {}, {
    data: makeReducer({
      limit: {
        enabled: false,
        users: -1
      }
    }, {
      [FORM_RESET + '/organizations.current']: (state) => {
        const data = cloneDeep(state)

        if (!data.limit) {
          data.limit = {}
        }
        
        data.limit.enable = data.limit.users > -1

        return data
      },
      [UPDATE_LIMIT]: (state, action) => {
        const orga = cloneDeep(state)

        orga.limit = action.enable ? {users: 1, enable: action.enable}: {users: -1, enable: action.enable}

        return orga
      }
    }),
    workspaces: makeListReducer('organizations.current.workspaces'),
    users: makeListReducer('organizations.current.users'),
    groups: makeListReducer('organizations.current.groups'),
    managers: makeListReducer('organizations.current.managers')
  })
})

export {
  reducer
}
