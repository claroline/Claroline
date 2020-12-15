import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {LIST_TOGGLE_SELECT, LIST_TOGGLE_SELECT_ALL} from '#/main/app/content/list/store/actions'

import {selectors} from '#/main/app/security/registration/store/selectors'
import {REGISTRATION_DATA_LOAD} from '#/main/app/security/registration/store/actions'

const getDefaultRole = (workspace) => workspace.registration.defaultRole

export const reducer = combineReducers({
  workspaces: makeListReducer(selectors.STORE_NAME+'.workspaces'),
  defaultWorkspaces: (state = null) => state,
  termOfService: makeReducer(null, {
    [REGISTRATION_DATA_LOAD]: (state, action) => action.data.termOfService || null
  }),
  facets: makeReducer([], {
    [REGISTRATION_DATA_LOAD]: (state, action) => action.data.facets || []
  }),
  options: makeReducer({}, {
    [REGISTRATION_DATA_LOAD]: (state, action) => action.data.options
  }),
  form: makeFormReducer(selectors.FORM_NAME, {
    new: true,
    data: {roles: [], code: null}
  }, {
    data: makeReducer({}, {
      [REGISTRATION_DATA_LOAD]: (state, action) => ({
        preferences: {
          locale: action.data.options.locale
        }
      }),
      [LIST_TOGGLE_SELECT+'/'+selectors.STORE_NAME+'.workspaces']: (state, action) => {
        const user = cloneDeep(state)

        action.selected ?
          user.roles.push(getDefaultRole(action.row)):
          user.roles.splice(user.roles.indexOf(role => role.id === getDefaultRole(action.row).id))

        return user
      },
      [LIST_TOGGLE_SELECT_ALL+'/'+selectors.STORE_NAME+'.workspaces']: (state, action) => {
        const user = cloneDeep(state)
        user.roles = action.rows.map(workspace => getDefaultRole(workspace))

        return user
      }
    })
  })
})
