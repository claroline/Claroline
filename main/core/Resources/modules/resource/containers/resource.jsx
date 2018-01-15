import React from 'react'
import {PropTypes as T} from 'prop-types'

import {connectPage} from '#/main/core/layout/page/connect'

import {select as resourceSelect} from '#/main/core/resource/selectors'
import {actions as resourceActions} from '#/main/core/resource/actions'
import {Resource as ResourceComponent} from '#/main/core/resource/components/resource.jsx'

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
 *
 * @param props
 * @constructor
 */
const Resource = props =>
  <ResourceComponent
    {...props}
  >
    {props.children}
  </ResourceComponent>

Resource.propTypes = {
  /**
   * Application of the resource.
   */
  children: T.node
}

const ResourceContainer = connectPage(
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
)(Resource)

export {
  ResourceContainer
}
