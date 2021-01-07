import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Provider} from 'react-redux'
import { useStore } from 'react-redux'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'

import {Router} from '#/main/app/router'

import {Popover} from '#/main/app/overlays/popover/components/popover'

// TODO : find a better way to give access to the app store and router (It uses a portal which is not mounted in the current tree)
const PopoverOverlay = props => {
  let store
  try {
    store = useStore()
  } catch (e) {
    store = null
  }

  if (!props.disabled) {
    return (
      <OverlayTrigger
        trigger="click"
        placement={props.position}
        rootClose={true}
        overlay={
          <Popover
            id={props.id}
            className={props.className}
            title={props.label}
          >
            {store &&
              <Provider store={store}>
                <Router>
                  {props.content}
                </Router>
              </Provider>
            }

            {!store &&
              <Router>
                {props.content}
              </Router>
            }
          </Popover>
        }
      >
        {props.children}
      </OverlayTrigger>
    )
  }

  return props.children
}

PopoverOverlay.propTypes = {
  id: T.string.isRequired,
  disabled: T.bool,
  children: T.element.isRequired,
  className: T.string,
  label: T.node,
  content: T.node.isRequired,
  position: T.oneOf(['top', 'right', 'bottom', 'left'])
}

PopoverOverlay.defaultProps = {
  position: 'top',
  disabled: false
}

export {
  PopoverOverlay
}
