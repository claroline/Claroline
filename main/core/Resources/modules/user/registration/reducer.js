
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {url} from '#/main/app/api'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {LIST_TOGGLE_SELECT, LIST_TOGGLE_SELECT_ALL} from '#/main/app/content/list/store/actions'
import cloneDeep from 'lodash/cloneDeep'

const getCollaboratorRole = (workspace) => workspace.roles.find(role => role.name.indexOf('COLLABORATOR') > -1)

export const reducer = {
  workspaces: makeListReducer('workspaces'),
  defaultWorkspaces: (state = null) => state,
  termOfService: (state = null) => state,
  facets: (state = []) => state,
  options: makeReducer({}, {
    /**
     * Redirects user after successful registration.
     * (It seems a little bit hacky to do it here but it's the simplest way to handle it).
     *
     * @param state
     */
    [FORM_SUBMIT_SUCCESS+'/user']: (state) => {
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
  user: makeFormReducer('user', {
    new: true,
    data: {roles: []}
  }, {
    data: makeReducer({}, {
      [LIST_TOGGLE_SELECT+'/workspaces']: (state, action) => {
        const user = cloneDeep(state)

        action.selected ?
          user.roles.push(getCollaboratorRole(action.row)):
          user.roles.splice(user.roles.indexOf(role => role.id === getCollaboratorRole(action.row).id))

        return user
      },
      [LIST_TOGGLE_SELECT_ALL+'/workspaces']: (state, action) => {
        const user = cloneDeep(state)
        user.roles = action.rows.map(workspace => getCollaboratorRole(workspace))

        return user
      }
    })
  })
}
