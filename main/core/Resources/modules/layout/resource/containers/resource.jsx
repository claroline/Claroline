import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {select as modalSelect} from '#/main/core/layout/modal/selectors'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {select as resourceSelect} from './../selectors'
import {actions as resourceActions} from './../actions'
import {Resource as ResourceComponent} from './../components/resource.jsx'

/**
 * Connected container for resources.
 *
 * Connects the <Resource> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 *
 * Requires the following reducers to be registered in your store :
 *   - modal
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
  children: T.node,

  modal: T.object.isRequired,
  resourceNode: T.object.isRequired
}

function mapStateToProps(state) {
  return {
    modal: modalSelect.modal(state),
    resourceNode: resourceSelect.resourceNode(state)
  }
}

// connects the container to redux
const ResourceContainer = connect(
  mapStateToProps,
  Object.assign(
    {},
    modalActions,
    resourceActions
  )
)(Resource)

export {
  ResourceContainer
}
