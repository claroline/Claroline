import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Page} from '#/main/core/layout/page/components/page'

/**
 * Connected container for pages.
 *
 * @param props
 * @constructor
 *
 * @deprecated
 */
const PageContainer = props =>
  <Page {...props}>
    {props.children}
  </Page>

PageContainer.propTypes = {
  embedded: T.bool,
  children: T.node.isRequired
}

export {
  PageContainer
}
