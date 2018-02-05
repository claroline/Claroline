import React from 'react'
import {PropTypes as T} from 'prop-types'

/**
 * Renders a subset of fields.
 */
const SubSet = props =>
  <div className="sub-fields">
    {props.children}
  </div>

SubSet.propTypes = {
  children: T.node.isRequired
}

export {
  SubSet
}
