import React from 'react'
import {PropTypes as T} from 'prop-types'

/**
 * Renders a fieldset if a condition is met.
 */
const ConditionalSet = props => props.condition &&
  <div className="sub-fields">
    {props.children}
  </div>

ConditionalSet.propTypes = {
  condition: T.bool,
  children: T.any.isRequired
}

export {
  ConditionalSet
}