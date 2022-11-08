import React, {cloneElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

const TypeDisplay = props => {
  if (!isEmpty(props.data) || !isEmpty(props.value)) {
    // because Details and Form do not share same api for now
    // remove later
    const value = props.data || props.value

    return (
      <article className="type-control">
        {value.icon && cloneElement(value.icon, {
          className: classes(value.icon.props.className, 'type-icon')
        })}

        <div role="presentation">
          <h1>{value.name}</h1>
          <p>{value.description}</p>
        </div>
      </article>
    )
  }

  return null
}

TypeDisplay.propTypes = {
  // for display
  data: T.shape({
    icon: T.node,
    name: T.string.isRequired,
    description: T.string
  }),
  // for input
  value: T.shape({
    icon: T.node,
    name: T.string.isRequired,
    description: T.string
  })
}

export {
  TypeDisplay
}
