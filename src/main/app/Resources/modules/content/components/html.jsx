import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

/**
 * Interprets and displays HTML content.
 */
const ContentHtml = props =>
  <div
    {...omit(props, 'children', 'align')}
    className={classes('content-html', `text-${props.align}`,props.className)}
    dangerouslySetInnerHTML={{ __html: props.children }}
    role="presentation"
  />

ContentHtml.propTypes = {
  /**
   * HTML content to display.
   */
  children: T.string.isRequired,

  /**
   * Additional classes to add to the DOM.
   */
  className: T.string,
  align: T.oneOf(['start', 'center', 'end', 'justify']).isRequired
}

ContentHtml.defaultProps = {
  align: 'justify'
}

export {
  ContentHtml
}
