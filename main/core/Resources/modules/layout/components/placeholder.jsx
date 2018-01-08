import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const PlaceholderWrapper = props =>
  <div className={classes(props.className, props.size && `placeholder-${props.size}`)}>
    {props.children}
  </div>

PlaceholderWrapper.propTypes = {
  className: T.string.isRequired,
  size: T.oneOf(['sm', 'lg']),
  children: T.node.isRequired
}

const EmptyPlaceholder = props =>
  <PlaceholderWrapper
    className="empty-placeholder"
    size={props.size}
  >
    <span className={`placeholder-icon ${props.icon}`} />

    <div className="placeholder-body">
      <span className="placeholder-title">{props.title}</span>

      {props.help &&
        <span className="placeholder-help">{props.help}</span>
      }
    </div>
  </PlaceholderWrapper>

EmptyPlaceholder.propTypes = {
  icon: T.string,
  title: T.string.isRequired,
  help: T.string,
  size: T.oneOf(['sm', 'lg'])
}

EmptyPlaceholder.defaultProps = {
  icon: 'fa fa-fw fa-hand-pointer-o',
  help: null
}

export {
  EmptyPlaceholder
}
