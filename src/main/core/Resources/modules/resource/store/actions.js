import get from 'lodash/get'

import {makeActionCreator, makeInstanceActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {actions as workspaceActions} from '#/main/app/contexts/workspace/store/actions'
import {selectors} from '#/main/core/resource/store/selectors'

// actions
export const RESOURCE_UPDATE_NODE          = 'RESOURCE_UPDATE_NODE'
export const RESOURCE_EVALUATION_UPDATE    = 'RESOURCE_EVALUATION_UPDATE'
export const RESOURCE_OPEN                 = 'RESOURCE_OPEN'
export const RESOURCE_LOAD                 = 'RESOURCE_LOAD'
export const RESOURCE_SET_LOADED           = 'RESOURCE_SET_LOADED'
export const RESOURCE_RESTRICTIONS_DISMISS = 'RESOURCE_RESTRICTIONS_DISMISS'
export const RESOURCE_NOT_FOUND            = 'RESOURCE_NOT_FOUND'

// action creators
export const actions = {}

actions.setResourceLoaded = makeActionCreator(RESOURCE_SET_LOADED, 'loaded')
actions.setNotFound = makeActionCreator(RESOURCE_NOT_FOUND)
actions.loadResource = makeActionCreator(RESOURCE_LOAD, 'resourceData')
actions.loadResourceType = makeInstanceActionCreator(RESOURCE_LOAD, 'resourceData')

actions.openResource = (resourceSlug) => (dispatch, getState) => {
  const currentSlug = selectors.slug(getState())
  if (currentSlug !== resourceSlug) {
    dispatch({
      type: RESOURCE_OPEN,
      resourceSlug: resourceSlug
    })
  }
}

actions.fetchResource = (slug, embedded = false) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: embedded ?
      ['claro_resource_load_embedded', {id: slug, embedded: embedded ? 1 : 0}] :
      ['claro_resource_load', {id: slug}],
    before: () => dispatch({
      type: RESOURCE_OPEN,
      resourceSlug: slug,
      embedded: embedded
    }),
    success: (response) => {
      // load resource base data
      dispatch(actions.loadResource(response))

      /*// load resource data inside the store
      dispatch(actions.loadResourceType(get(response, 'resourceNode.meta.type'), response))

      // mark the resource as loaded
      // it's done through another action (not RESOURCE_LOAD) to be sure all reducers have been resolved
      // and store is up-to-date
      dispatch(actions.setResourceLoaded(true))*/
    },
    error: (response, status) => {
      switch (status) {
        case 404:
          dispatch(actions.setNotFound())
          break
        case 401:
        case 403:
          // we don't have any custom resource type data here
          dispatch(actions.loadResource(response)) // the response contains why we can't access the resource
          dispatch(actions.setResourceLoaded(true))
          break
      }
    }
  }
})

actions.updateNode = makeActionCreator(RESOURCE_UPDATE_NODE, 'resourceNode')

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

actions.updateUserEvaluation = (userEvaluation) => (dispatch) => {
  dispatch(workspaceActions.fetchCurrentEvaluation())

  return dispatch({
    type: RESOURCE_EVALUATION_UPDATE,
    userEvaluation: userEvaluation
  })
}

actions.dismissRestrictions = makeActionCreator(RESOURCE_RESTRICTIONS_DISMISS)

actions.checkAccessCode = (resourceNode, code) => (dispatch) => dispatch({
  [API_REQUEST] : {
    url: ['claro_resource_unlock', {id: resourceNode.id}],
    request: {
      method: 'POST',
      body: JSON.stringify({code: code})
    },
    success: () => dispatch(actions.setResourceLoaded(false)) // force reload the resource
  }
})
