import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {HelpBlock} from './help-block.jsx'

const FlagGroup = props =>
  <div className={classes('checkbox', props.className)}>
    <label htmlFor={props.controlId}>
      <input
        id={props.controlId}
        type="checkbox"
        checked={props.active}
        onChange={() => props.onChange(!props.active)}
      />

      {props.active ?
        props.activeLabel || props.label :
        props.label
      }
    </label>

    {props.help &&
      <HelpBlock help={props.help} />
    }
  </div>

FlagGroup.propTypes = {
  className: T.string,
  controlId: T.string.isRequired,
  label: T.string.isRequired,
  activeLabel: T.string,
  help: T.string,
  active: T.bool,
  onChange: T.func.isRequired
}

FlagGroup.defaultProps = {
  active: false
}

export {
  FlagGroup
}
