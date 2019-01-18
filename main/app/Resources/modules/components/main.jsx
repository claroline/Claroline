import React, {Fragment} from 'react'
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
    <Router embedded={props.embedded}>
      <Fragment>
        <AlertOverlay key="alert" />

        {props.children}

        <OverlayStack>
          <ModalOverlay key="modal" />,
          <WalkthroughOverlay key="walkthrough" />
        </OverlayStack>
      </Fragment>
    </Router>
  </Provider>

Main.propTypes = {
  embedded: T.bool,
  store: T.shape({

  }).isRequired,
  children: T.any
}

Main.defaultProps = {
  embedded: false
}

export {
  Main
}
