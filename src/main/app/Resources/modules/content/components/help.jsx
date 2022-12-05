import React from 'react'
import {PropTypes as T} from 'prop-types'

import {toKey} from '#/main/core/scaffolding/text'

const ContentHelp = props => Array.isArray(props.help) ?
  <ul className="help-block-list" style={props.style}>
    {props.help.map(help =>
      <li key={toKey(help)} className="help-block">
        <span className="help-icon fa fa-fw fa-circle-info" />
        {help}
      </li>
    )}
  </ul>
  :
  <div className="help-block" style={props.style}>
    <span className="help-icon fa fa-fw fa-circle-info" />
    {props.help}
  </div>

ContentHelp.propTypes = {
  style: T.object,
  help: T.oneOfType([
    T.string,           // a single help message
    T.arrayOf(T.string) // a list of help messages
  ]).isRequired
}

export {
  ContentHelp
}
