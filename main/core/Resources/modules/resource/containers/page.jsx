import React from 'react'

import {connectPage} from '#/main/core/layout/page/connect'

import {select as resourceSelect} from '#/main/core/resource/selectors'
import {actions as resourceActions} from '#/main/core/resource/actions'
import {ResourcePage} from '#/main/core/resource/components/page.jsx'

/**
 * Connected container for resources.
 *
 * Connects the <Resource> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 *
 * Requires the following reducers to be registered in your store (@see makePageReducer) :
 *   - modal
 *   - alerts [optional]
 *   - resource
 */
const ResourcePageContainer = connectPage(
  (state) => ({
    resourceNode: resourceSelect.resourceNode(state)
  }),
  (dispatch) => ({
    updateNode(resourceNode) {
      dispatch(resourceActions.updateNode(resourceNode))
    },
    updatePublication(resourceNode) {
      dispatch(resourceActions.updatePublication(resourceNode))
    },
    togglePublication(resourceNode) {
      dispatch(resourceActions.togglePublication(resourceNode))
    }
  })
)(ResourcePage)

export {
  ResourcePageContainer
}
