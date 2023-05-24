import React, {forwardRef} from 'react'
import {PropTypes as T} from 'prop-types'

import Dropdown from 'react-bootstrap/Dropdown'

const MenuOverlay = forwardRef((props, ref) =>
  <Dropdown
    id={props.id}
    open={props.open}
    //drop={'right' === props.align}
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
  open: T.bool,
  className: T.string,
  disabled: T.bool,
  //position: T.oneOf(['top', 'bottom']),
  align: T.oneOf(['left', 'right']),
  children: T.node.isRequired,
  onToggle: T.func
}

MenuOverlay.defaultProps = {
  disabled: false,
  //position: 'bottom',
  align: 'left'
}

export {
  MenuOverlay
}
