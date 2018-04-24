import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const Heading = props =>
  React.createElement(`h${props.level}`, Object.assign({},
    omit(props, 'level', 'displayLevel', 'first'),
    {
      className: classes(
        props.className,
        props.displayLevel && `h${props.displayLevel}`,
        props.first && 'h-first'
      )
    }
  ), props.children)

Heading.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  first: T.bool,
  children: T.any.isRequired
}

Heading.defaultProps = {
  first: false
}

export {
  Heading
}
