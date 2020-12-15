import React from 'react'
import {PropTypes as T} from 'prop-types'

const IconDisplay = (props) =>
  <span className={`fa fa-fw fa-${props.data}`} />

IconDisplay.propTypes = {
  data: T.string.isRequired
}

export {
  IconDisplay
}
