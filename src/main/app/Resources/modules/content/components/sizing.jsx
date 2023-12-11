import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const ContentSizing = (props) =>
  <div className={classes(props.className, {
    'content-sm': 'sm' === props.size,
    'content-md': 'md' === props.size,
    'content-lg': 'lg' === props.size,
    'content-full': 'full' === props.size
  })}>
    {props.children}
  </div>

ContentSizing.propTypes = {
  className: T.string,
  size: T.oneOf(['sm', 'md', 'lg', 'full']),
  children: T.any
}

export {
  ContentSizing
}
