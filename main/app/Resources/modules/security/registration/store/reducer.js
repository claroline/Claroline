import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {url} from '#/main/app/api'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
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
    [REGISTRATION_DATA_LOAD]: (state, action) => action.data.options,
    /**
     * Redirects user after successful registration.
     * (It seems a little bit hacky to do it here but it's the simplest way to handle it).
     *
     * @param state
     */
    [FORM_SUBMIT_SUCCESS+'/'+selectors.FORM_NAME]: (state) => {
      if (state.redirectAfterLoginUrl) {
        window.location = state.redirectAfterLoginUrl
      } else {
        switch (state.redirectAfterLoginOption) {
          case 'DESKTOP':
            window.location = url(['claro_desktop_open'])
        }
      }
    }
  }),
  form: makeFormReducer(selectors.FORM_NAME, {
    new: true,
    data: {roles: [], code: null}
  }, {
    data: makeReducer({}, {
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
