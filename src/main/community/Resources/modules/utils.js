import uniqBy from 'lodash/uniqBy'
import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/community/constants'

function displayUsername(user = null) {
  if (user) {
    return user.firstName + ' ' + user.lastName
  }

  return trans('unknown')
}

function getPlatformRoles(roles) {
  return uniqBy(roles.filter(role => constants.ROLE_PLATFORM === role.type), 'id')
}

function getWorkspaceRoles(roles, workspaceId) {
  return uniqBy(roles.filter(role => constants.ROLE_WORKSPACE === role.type && -1 !== role.name.indexOf(workspaceId)), 'id')
}

export {
  displayUsername,
  getPlatformRoles,
  getWorkspaceRoles
}
