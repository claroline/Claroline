import get from 'lodash/get'
import {connect} from 'react-redux'

import {queryString} from '#/main/app/api/router'
import {selectors as listSelectors} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as communitySelectors} from '#/main/core/tools/community/store'
import {UserTab as UserTabComponent} from '#/main/core/tools/community/user/components/tab'
import {actions, selectors} from '#/main/core/tools/community/user/store'

const UserTab = connect(
  state => {
    const contextData = toolSelectors.contextData(state)

    // build csv export query string based on the current list config
    let listQueryString = listSelectors.queryString(listSelectors.list(state, selectors.LIST_NAME))

    const params = {
      columns: [
        'firstName',
        'lastName',
        'username',
        'email',
        'administrativeCode',
        'meta.lastActivity',
        'roles'
      ]
    }

    if (get(contextData, 'id', null)) {
      params.filters = {
        workspace: get(contextData, 'id')
      }
    }

    listQueryString += '&'+queryString(params)

    return {
      path: toolSelectors.path(state),
      listQueryString: listQueryString,
      contextType: toolSelectors.contextType(state),
      contextData: contextData,
      canCreate: communitySelectors.canCreate(state),
      defaultRole: communitySelectors.defaultRole(state),
      limitReached: selectors.limitReached(state)
    }
  },
  dispatch => ({
    open(id = null, defaultRole) {
      dispatch(actions.open(selectors.FORM_NAME, id, {
        organization: null, // retrieve it with axel stuff
        roles: defaultRole ? [defaultRole] : []
      }))
    },
    addUsersToRoles(roles, users) {
      roles.map(role => dispatch(actions.addUsersToRole(role, users)))
    }
  })
)(UserTabComponent)

export {
  UserTab
}
