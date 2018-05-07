import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const Heading = props =>
  React.createElement(`h${props.level}`, Object.assign({},
    omit(props, 'level', 'displayLevel', 'first', 'displayed'),
    {
      className: classes(
        props.className,
        props.displayLevel && `h${props.displayLevel}`,
        props.first && 'h-first',
        !props.displayed && 'sr-only'
      )
    }
  ), props.children)

Heading.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  first: T.bool,
  displayed: T.bool,
  children: T.any.isRequired
}

Heading.defaultProps = {
  first: false,
  displayed: true
}

export {
  Heading
}
