import React from 'react'
import {PropTypes as T} from 'prop-types'

// TODO : reuse EmailButton

const EmailDisplay = props =>
  <a href={`mailto:${props.data}`}>
    {props.data}
  </a>

EmailDisplay.propTypes = {
  data: T.string.isRequired
}

export {
  EmailDisplay
}
