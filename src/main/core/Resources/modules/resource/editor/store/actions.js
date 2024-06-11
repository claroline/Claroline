import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {actions as resourceActions} from '#/main/core/resource/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors} from '#/main/core/resource/editor/store/selectors'

export const SET_RIGHTS_RECURSIVE = 'SET_RIGHTS_RECURSIVE'

export const actions = {}

actions.refresh = (resourceType, resourceData) => (dispatch) => {
  dispatch(resourceActions.loadResource(resourceData))
  dispatch(resourceActions.loadResourceType(resourceType, resourceData))
}

actions.update = (value, propPath = null) => {
  if (propPath) {
    return formActions.updateProp(selectors.STORE_NAME, propPath, value)
  }

  return formActions.update(selectors.STORE_NAME, value)
}

actions.updateResourceNode = (value, propPath = null) => {
  if (propPath) {
    return formActions.updateProp(selectors.STORE_NAME, 'resourceNode.'+propPath, value)
  }

  return formActions.updateProp(selectors.STORE_NAME, 'resourceNode', value)
}

actions.updateResource = (value, propPath = null) => {
  if (propPath) {
    return formActions.updateProp(selectors.STORE_NAME, 'resource.'+propPath, value)
  }

  return formActions.updateProp(selectors.STORE_NAME, 'resource', value)
}

actions.setRecursive = makeActionCreator(SET_RIGHTS_RECURSIVE, 'recursiveEnabled')

actions.fetchRights = (resourceNode) => ({
  [API_REQUEST]: {
    url: ['apiv2_resource_get_rights', {id: resourceNode.id}]
  }
})
