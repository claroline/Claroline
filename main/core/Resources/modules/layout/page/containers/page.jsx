import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Router} from '#/main/app/router'
import {ModalOverlay} from '#/main/app/overlay/modal/containers/overlay'
import {AlertOverlay} from '#/main/app/overlay/alert/containers/overlay'

import {Page} from '#/main/core/layout/page/components/page'

/**
 * Connected container for pages.
 *
 * @param props
 * @constructor
 */
const PageContainer = props =>
  <Router embedded={props.embedded}>
    <Page {...props}>
      <AlertOverlay />

      {props.children}

      <ModalOverlay />
    </Page>
  </Router>

PageContainer.propTypes = {
  embedded: T.bool,
  children: T.node.isRequired
}

export {
  PageContainer
}
