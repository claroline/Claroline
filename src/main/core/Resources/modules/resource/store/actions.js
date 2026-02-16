import {makeActionCreator, makeInstanceActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {selectors} from '#/main/core/resource/store/selectors'

// actions
export const RESOURCE_EVALUATION_UPDATE    = 'RESOURCE_EVALUATION_UPDATE'
export const RESOURCE_OPEN                 = 'RESOURCE_OPEN'
export const RESOURCE_LOAD                 = 'RESOURCE_LOAD'
export const RESOURCE_SET_LOADED           = 'RESOURCE_SET_LOADED'
export const RESOURCE_SET_ERROR = 'RESOURCE_SET_ERROR'

// action creators
export const actions = {}

actions.setResourceLoaded = makeActionCreator(RESOURCE_SET_LOADED, 'loaded')
actions.setError = makeActionCreator(RESOURCE_SET_ERROR, 'code', 'message', 'additional')
actions.loadResource = makeActionCreator(RESOURCE_LOAD, 'resourceData')
actions.loadResourceType = makeInstanceActionCreator(RESOURCE_LOAD, 'resourceData')
actions.reload = () => actions.setResourceLoaded(false)
actions.open = (slug, embedded = false) => ({
  type: RESOURCE_OPEN,
  resourceSlug: slug,
  embedded: embedded
})

actions.fetchResource = (slug, embedded = false) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    silentError: true,
    url: embedded ?
      ['claro_resource_load_embedded', {id: slug, embedded: embedded ? 1 : 0}] :
      ['claro_resource_load', {id: slug}],
    success: (response) => dispatch(actions.loadResource(response)),
    error: (response, status) => {
      switch (status) {
        case 404:
          dispatch(actions.setError('NOT_FOUND', 'Resource not found.', null))
          break
        case 500:
          dispatch(actions.setError('UNKNOWN_ERROR', response.message, response.trace))
          break
        case 401:
        case 403:
          // the response contains why we can't access the resource and the minimal representation of the resource node
          dispatch(actions.loadResource(response))
          break
      }
    }
  }
})

actions.triggerLifecycleAction = (action) => (dispatch, getState) => {
  const lifecycleActions = selectors.resourceLifecycle(getState())

  // checks if the current resource implements the action
  if (lifecycleActions[action]) {
    // dispatch the implemented action with resourceNode as param (don't know if this is useful)
    return lifecycleActions[action](
      selectors.resourceNode(getState())
    )
  }
}

actions.updateUserEvaluation = makeActionCreator(RESOURCE_EVALUATION_UPDATE, 'userEvaluation')

actions.checkAccessCode = (resourceNode, code) => (dispatch) => dispatch({
  [API_REQUEST] : {
    url: ['claro_resource_unlock', {id: resourceNode.id}],
    request: {
      method: 'POST',
      body: JSON.stringify({code: code})
    },
    success: () => dispatch(actions.reload()) // force the reload of the resource
  }
})
