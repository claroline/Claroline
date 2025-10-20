import {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {isHtmlEmpty} from '#/main/app/data/types/html/validators'

/**
 * Interprets and displays HTML content.
 */
const Html = props => {
  if (!props.children || isHtmlEmpty(props.children)) {
    return null
  }

  return createElement(props.as || 'div', {
    ...omit(props, 'as', 'children', 'align'),
    className: classes('content-html', props.align && `text-${props.align}`, props.className),
    dangerouslySetInnerHTML: { __html: props.children }
  })
}

Html.propTypes = {
  /**
   * Element used to wrap the HTML content. Default: div.
   */
  as: T.elementType,

  /**
   * HTML content to display.
   */
  children: T.string,

  /**
   * Additional classes to add to the DOM.
   */
  className: T.string,
  align: T.oneOf(['start', 'center', 'end', 'justify'])
}

export {
  Html
}
