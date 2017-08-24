import React from 'react'
import {PropTypes as T} from 'prop-types'

const HelpBlock = props =>
  <span className="help-block">
    <span className="fa fa-info-circle" />
    {props.help}
  </span>

HelpBlock.propTypes = {
  help: T.string.isRequired
}

export {
  HelpBlock
}
