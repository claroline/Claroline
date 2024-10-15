import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

const Phone = (props) =>
  <a
    {...omit(props, 'phone')}
     href={`mailto:${props.phone}`}
  >
    {props.phone}
  </a>

Phone.propTypes = {
  phone: T.string.isRequired
}

export {
  Phone
}
