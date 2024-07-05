import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const ContentSizing = (props) =>
  <div className={classes(props.className, `content-${props.size}`)} role="presentation">
    {props.children}
  </div>

ContentSizing.propTypes = {
  className: T.string,
  size: T.oneOf(['sm', 'md', 'lg', 'full']),
  children: T.any
}

ContentSizing.defaultProps = {
  size: 'md'
}

export {
  ContentSizing
}
