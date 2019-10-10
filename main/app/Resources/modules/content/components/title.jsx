import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const HeadingWrapper = props  =>
  React.createElement(`h${props.level}`, Object.assign({},
    omit(props, 'level', 'displayLevel', 'displayed', 'align'),
    {
      className: classes(
        props.className,
        props.displayLevel && `h${props.displayLevel}`,
        !props.displayed && 'sr-only',
        `text-${props.align}`
      )
    }
  ), props.children)

HeadingWrapper.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  displayed: T.bool,
  align: T.oneOf(['left', 'center', 'right']),
  children: T.any.isRequired
}

HeadingWrapper.defaultProps = {
  displayed: true,
  align: 'left'
}

const ContentTitle = props =>
  <HeadingWrapper
    {...omit(props, 'numbering', 'title', 'subtitle')}
  >
    {props.numbering &&
      <span className="h-numbering">{props.numbering}</span>
    }

    {props.title}

    {props.subtitle &&
      <small>{props.subtitle}</small>
    }

    {props.children}
  </HeadingWrapper>

ContentTitle.propTypes = {
  className: T.string,
  level: T.number.isRequired,
  displayLevel: T.number,
  numbering: T.string,
  title: T.string.isRequired,
  subtitle: T.string,
  displayed: T.bool,
  align: T.oneOf(['left', 'center', 'right']),
  children: T.any
}

export {
  ContentTitle
}
