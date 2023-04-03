import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

/**
 *
 * @deprecated
 */
const Heading = props =>
  React.createElement(`h${props.level}`, Object.assign({},
    omit(props, 'level', 'displayLevel', 'first', 'displayed', 'align'),
    {
      className: classes(
        props.className,
        props.displayLevel && `h${props.displayLevel}`,
        props.first && 'h-first',
        !props.displayed && 'sr-only',
        `text-${props.align}`
      )
    }
  ), props.children)

Heading.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  first: T.bool,
  displayed: T.bool,
  align: T.oneOf(['left', 'center', 'right']),
  children: T.any.isRequired
}

Heading.defaultProps = {
  first: false,
  displayed: true,
  align: 'left'
}

export {
  Heading
}
