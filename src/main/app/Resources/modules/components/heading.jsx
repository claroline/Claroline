import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const Heading = props  => createElement(`h${props.level}`, Object.assign({},
  omit(props, 'title', 'subtitle', 'level', 'displayLevel', 'displayed', 'align'),
  {
    className: classes(
      props.className,
      props.displayLevel && `h${props.displayLevel}`,
      {
        'text-start': 'left' === props.align,
        'text-center': 'center' === props.align,
        'text-end': 'right' === props.align
      }
    )
  }
), [props.title, props.subtitle ? <small key="heading-subtitle">{props.subtitle}</small>:undefined])

Heading.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  title: T.string.isRequired,
  subtitle: T.node,
  align: T.oneOf(['left', 'center', 'right'])
}

export {
  Heading
}
