import React, {createElement, useId, useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'
import random from 'lodash/random'

const HeadingSkeleton = (props) => {
  const id = useId()
  const width = useMemo(() => {
    return random(5, 11)
  }, [id])

  return (
    createElement('h'+props.level, {
      className: classes('placeholder-glow', props.displayLevel && 'h'+props.displayLevel, {
        'text-end': 'end' === props.align
      })
    }, <span className={classes('placeholder rounded-1', `col-${width}`, props.className)} />)
  )
}

HeadingSkeleton.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  align: T.oneOf(['start', 'center', 'end', 'justified'])
}

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
  Heading,
  HeadingSkeleton
}
