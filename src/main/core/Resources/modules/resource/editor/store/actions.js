import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {actions as resourceActions} from '#/main/core/resource/store/actions'

export const SET_RIGHTS_RECURSIVE = 'SET_RIGHTS_RECURSIVE'

export const actions = {}

actions.refresh = (resourceType, resourceData) => (dispatch) => {
  dispatch(resourceActions.loadResource(resourceData))
  dispatch(resourceActions.loadResourceType(resourceType, resourceData))
}

actions.setRecursive = makeActionCreator(SET_RIGHTS_RECURSIVE, 'recursiveEnabled')

actions.fetchRights = (resourceNode) => ({
  [API_REQUEST]: {
    url: ['apiv2_resource_get_rights', {id: resourceNode.id}]
  }
})
