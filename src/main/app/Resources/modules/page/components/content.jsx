import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

/**
 * Content of the current page.
 */
const PageContent = props =>
  <div role="presentation" className={classes('page-content', props.className)}>
    {props.children}
  </div>

PageContent.propTypes = {
  className: T.string,

  /**
   * Content to display in the page.
   */
  children: T.node.isRequired
}

export {
  PageContent
}
