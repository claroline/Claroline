import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {select as modalSelect} from '#/main/core/layout/modal/selectors'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {select as resourceSelect} from './../selectors'
import {actions as resourceActions} from './../actions'
import {Resource} from '../components/resource.jsx'

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
const ResourceContainer = props =>
  <Resource
    {...props}
  >
    {props.children}
  </Resource>

ResourceContainer.propTypes = {
  /**
   * Application of the resource.
   */
  children: T.node,

  /**
   * Current displayed modal if any.
   */
  modal: T.shape({
    type: T.string,
    fading: T.bool.isRequired,
    props: T.object.isRequired
  }),

  /**
   * Builds the current modal component.
   */
  createModal: T.func.isRequired,

  /**
   * Shows a modal.
   */
  showModal: T.func.isRequired,

  /**
   * Hides the current displayed modal.
   */
  fadeModal: T.func.isRequired,

  customActions: T.array.isRequired,
  editMode: T.bool,
  edit: T.oneOfType([T.func, T.string]).isRequired,
  save: T.object.isRequired,

  /**
   * Changes publication status of the resource.
   */
  togglePublication: T.func.isRequired,

  /**
   * Updates the resource node properties.
   *
   * @param {object} resourceNode - the new resourceNode properties
   */
  updateNode: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    modal: modalSelect.modal(state),
    resourceNode: resourceSelect.resourceNode(state)
  }
}

// connects the container to redux
const ConnectedResource = connect(
  mapStateToProps,
  Object.assign(
    {},
    modalActions,
    resourceActions
  )
)(Resource)

export {ConnectedResource as Resource}
