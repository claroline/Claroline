import React from 'react'
import {PropTypes as T} from 'prop-types'

const EmailLink = props =>
  <a href={`mailto:${props.data}`}>
    {props.data}
  </a>

EmailLink.propTypes = {
  data: T.string.isRequired
}

export {
  EmailLink
}
