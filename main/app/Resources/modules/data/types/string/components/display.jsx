import React from 'react'
import {PropTypes as T} from 'prop-types'

const StringDisplay = (props) =>
  <div id={props.id} className="string-display text-justify">
    {props.data}
  </div>

StringDisplay.propTypes = {
  id: T.string.isRequired,
  data: T.string.isRequired
}

export {
  StringDisplay
}
