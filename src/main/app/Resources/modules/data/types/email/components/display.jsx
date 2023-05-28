import React from 'react'
import {PropTypes as T} from 'prop-types'

const EmailDisplay = (props) =>
  <a id={props.id} href={`mailto:${props.data}`} className="d-block">
    {props.data}
  </a>

EmailDisplay.propTypes = {
  id: T.string.isRequired,
  data: T.string.isRequired
}

export {
  EmailDisplay
}
