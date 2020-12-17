import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Provider, ReactReduxContext} from 'react-redux'

import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'

import {Router} from '#/main/app/router'

import {Popover} from '#/main/app/overlays/popover/components/popover'

// TODO : find a better way to give access to the app store and router (It uses a portal which is not mounted in the current tree)
const PopoverOverlay = props => !props.disabled ?
  <ReactReduxContext.Consumer>
    {({ store }) => (
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
            <Provider store={store}>
              <Router>
                {props.content}
              </Router>
            </Provider>
          </Popover>
        }
      >
        {props.children}
      </OverlayTrigger>
    )}
  </ReactReduxContext.Consumer>
  :
  props.children

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
