import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const PlaceholderWrapper = props =>
  <div id={props.id} className={classes('empty-placeholder', props.className, props.size && `empty-placeholder-${props.size}`)} style={props.style}>
    {props.children}
  </div>

PlaceholderWrapper.propTypes = {
  id: T.string,
  className: T.string,
  size: T.oneOf(['sm', 'md', 'lg']),
  style: T.object,
  children: T.node.isRequired
}

const ContentPlaceholder = props =>
  <PlaceholderWrapper
    id={props.id}
    className={props.className}
    size={props.size}
    style={props.style}
  >
    {props.icon &&
      <span className={`placeholder-icon ${props.icon}`} />
    }

    <div className="placeholder-body">
      <span className="placeholder-title">{props.title}</span>

      {props.help &&
        <span className="placeholder-help">{props.help}</span>
      }

      {props.children}
    </div>
  </PlaceholderWrapper>

ContentPlaceholder.propTypes = {
  id: T.string,
  icon: T.string,
  title: T.string.isRequired,
  className: T.string,
  help: T.string,
  size: T.oneOf(['sm', 'md', 'lg']),
  style: T.object,
  children: T.node
}

ContentPlaceholder.defaultProps = {
  help: null,
  size: 'md'
}

export {
  ContentPlaceholder
}
