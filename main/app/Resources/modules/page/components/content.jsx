import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

/**
 * Content of the current page.
 */
const PageContent = props =>
  <div className={classes('page-content', props.className, {
    'page-content-shift': props.headerSpacer
  })}>
    {props.children}
  </div>

PageContent.propTypes = {
  className: T.string,
  headerSpacer: T.bool,

  /**
   * Content to display in the page.
   */
  children: T.node.isRequired
}

PageContent.defaultProps = {
  headerSpacer: true
}

export {
  PageContent
}
