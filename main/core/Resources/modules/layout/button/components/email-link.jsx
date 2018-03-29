import React from 'react'
import {PropTypes as T} from 'prop-types'

const EmailLink = props =>
  <a href={`mailto:${props.data}`}>
    {props.data}
  </a>

EmailLink.propTypes = {
  // it's named `data` to be able to use it as is in Data* representation
  data: T.string.isRequired
}

export {
  EmailLink
}
