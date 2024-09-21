import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const Heading = props  => createElement(`h${props.level}`, Object.assign({},
  omit(props, 'level', 'displayLevel', 'displayed', 'align'),
  {
    className: classes(
      props.className,
      !props.displayed && 'visually-hidden',
      props.displayLevel && `h${props.displayLevel}`,
      props.align && `text-${props.align}`
    )
  }
), props.children)

Heading.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayed: T.bool,
  displayLevel: T.number,
  align: T.oneOf(['start', 'center', 'end']),
  children: T.node.isRequired
}

Heading.defaultProps = {
  displayed: true
}

export {
  Heading
}
