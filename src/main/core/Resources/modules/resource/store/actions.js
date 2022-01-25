import {makeActionCreator, makeInstanceActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {selectors} from '#/main/core/resource/store/selectors'

// actions
export const RESOURCE_UPDATE_NODE          = 'RESOURCE_UPDATE_NODE'
export const USER_EVALUATION_UPDATE        = 'USER_EVALUATION_UPDATE'
export const RESOURCE_OPEN                 = 'RESOURCE_OPEN'
export const RESOURCE_LOAD                 = 'RESOURCE_LOAD'
export const RESOURCE_SET_LOADED           = 'RESOURCE_SET_LOADED'
export const RESOURCE_RESTRICTIONS_DISMISS = 'RESOURCE_RESTRICTIONS_DISMISS'
export const RESOURCE_NOT_FOUND            = 'RESOURCE_NOT_FOUND'

// this ones should not be here
export const RESOURCE_COMMENT_ADD          = 'RESOURCE_COMMENT_ADD'
export const RESOURCE_COMMENT_UPDATE       = 'RESOURCE_COMMENT_UPDATE'
export const RESOURCE_COMMENT_REMOVE       = 'RESOURCE_COMMENT_REMOVE'

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

actions.fetchResource = (slug, embedded = false, loadApp) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_resource_load_embedded', {id: slug, embedded: embedded ? 1 : 0}],
    success: (response) => {
      // weird time : I need to mount the correct resource type app before continuing loading data in store
      // in order to have the custom store of the resource mounted
      loadApp(response.resourceNode.meta.type).then(() => {
        dispatch(actions.loadResource(response))

        // load resource data inside the store
        dispatch(actions.loadResourceType(response.resourceNode.meta.type, response))

        // mark the resource as loaded
        // it's done through another action (not RESOURCE_LOAD) to be sure all reducers have been resolved
        // and store is up-to-date
        dispatch(actions.setResourceLoaded(true))
      })
    },
    error: (response, status) => {
      switch (status) {
        case 404:
          dispatch(actions.setNotFound())
          break
        case 401:
        case 403:
          loadApp(response.resourceNode.meta.type).then(() => {
            dispatch(actions.loadResource(response))
            dispatch(actions.setResourceLoaded(true))
          })
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

actions.updateUserEvaluation = makeActionCreator(USER_EVALUATION_UPDATE, 'userEvaluation')

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

actions.closeResource = (slug, embedded = false) => ({
  [API_REQUEST] : {
    silent: true,
    url: ['claro_resource_close', {slug: slug}],
    request: {
      method: 'PUT',
      body: JSON.stringify({embedded: embedded})
    }
  }
})

// this ones should not be here
actions.addResourceComment = makeActionCreator(RESOURCE_COMMENT_ADD, 'comment')
actions.updateResourceComment = makeActionCreator(RESOURCE_COMMENT_UPDATE, 'comment')
actions.removeResourceComment = makeActionCreator(RESOURCE_COMMENT_REMOVE, 'commentId')
