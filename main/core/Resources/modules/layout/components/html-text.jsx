import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

/**
 * Interprets and displays HTML content.
 *
 * @param props
 * @constructor
 */
const HtmlText = props =>
  <div
    className={classes('text-html-content', props.className)}
    dangerouslySetInnerHTML={{ __html: props.children }}
  />

HtmlText.propTypes = {
  /**
   * HTML content to display.
   */
  children: T.string.isRequired,

  /**
   * Additional classes to add to the DOM.
   */
  className: T.string
}

export {
  HtmlText
}
