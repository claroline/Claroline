import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {select as modalSelect} from '#/main/core/layout/modal/selectors'
import {actions as modalActions} from '#/main/core/layout/modal/actions'

import {Page as PageComponent} from '#/main/core/layout/page/components/page.jsx'

/**
 * Connected container for pages.
 *
 * Connects the <Page> component to a redux store.
 * If you don't use redux in your implementation @see Page functional component.
 *
 * Requires the following reducers to be registered in your store :
 *   - modal
 *
 * @param props
 * @constructor
 */
const Page = props =>
  <PageComponent
    {...props}
  >
    {props.children}
  </PageComponent>

Page.propTypes = {
  /**
   * Content to display in the page.
   */
  children: T.node.isRequired
}


function mapStateToProps(state) {
  return {
    modal: modalSelect.modal(state)
  }
}

// connects the container to redux
const PageContainer = connect(mapStateToProps, Object.assign({}, modalActions))(Page)

export {
  PageContainer
}
