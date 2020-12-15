import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

/**
 * Interprets and displays HTML content.
 *
 * @param props
 * @constructor
 */
const ContentHtml = props =>
  <div
    {...omit(props, 'children')}
    className={classes('text-html-content text-justify', props.className)}
    dangerouslySetInnerHTML={{ __html: props.children }}
  />

ContentHtml.propTypes = {
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
  ContentHtml
}
