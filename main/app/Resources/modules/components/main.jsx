import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Provider} from 'react-redux'

import {Router} from '#/main/app/router'
import {OverlayStack} from '#/main/app/overlay/containers/stack'

// implemented overlays
import {ModalOverlay} from '#/main/app/overlay/modal/containers/overlay'
import {AlertOverlay} from '#/main/app/overlay/alert/containers/overlay'
import {WalkthroughOverlay} from '#/main/app/overlay/walkthrough/containers/overlay'

// TODO : maybe append app styles here

const Main = props =>
  <Provider store={props.store}>
    <AlertOverlay key="alert" />

    <Router embedded={props.embedded}>
      {props.children}
    </Router>

    <OverlayStack>
      <ModalOverlay key="modal" />,
      <WalkthroughOverlay key="walkthrough" />
    </OverlayStack>
  </Provider>

Main.propTypes = {
  embedded: T.bool,
  store: T.object.isRequired,
  children: T.any
}

Main.defaultProps = {
  embedded: false
}

export {
  Main
}
