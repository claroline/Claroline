import React, {forwardRef} from 'react'
import {PropTypes as T} from 'prop-types'

import Dropdown from 'react-bootstrap/Dropdown'

// forwardRef is required for tooltip
const MenuOverlay = forwardRef((props, ref) =>
  <Dropdown
    id={props.id}
    show={props.show}
    drop={'top' === props.position ? 'up' : 'down'}
    autoClose={true}
    className={props.className}
    disabled={props.disabled}
    onToggle={props.onToggle}
    ref={ref}
  >
    {props.children}
  </Dropdown>
)

MenuOverlay.propTypes = {
  id: T.string.isRequired,
  show: T.bool,
  className: T.string,
  disabled: T.bool,
  position: T.oneOf(['top', 'bottom']),
  children: T.node.isRequired,
  onToggle: T.func
}

MenuOverlay.defaultProps = {
  disabled: false,
  position: 'bottom'
}

export {
  MenuOverlay
}
