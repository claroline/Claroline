import React from 'react'
import {PropTypes as T} from 'prop-types'

export const WarningIcon = props => {
  return props.valid ?
     <span className="fa fa-check answer-warning-span" aria-hidden="true"></span> :
     <span className="fa fa-times answer-warning-span" aria-hidden="true"></span>
}

WarningIcon.propTypes = {
  valid: T.bool.isRequired
}
