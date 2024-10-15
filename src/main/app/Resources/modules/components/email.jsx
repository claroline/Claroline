import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

const Email = (props) =>
  <a
    {...omit(props, 'email')}
     href={`mailto:${props.email}`}
  >
    {props.email}
  </a>

Email.propTypes = {
  email: T.string.isRequired
}

export {
  Email
}
