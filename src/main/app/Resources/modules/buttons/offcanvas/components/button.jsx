import React, {createElement, forwardRef, useState} from 'react'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {Offcanvas} from '#/main/app/overlays/offcanvas'

const OffcanvasButton = forwardRef((props, ref) => {
  const [show, setShow] = useState(false)
  const handleClose = () => setShow(false)
  const handleShow = () => setShow(true)

  return (
    <>
      <CallbackButton
        {...omit(props, 'offcanvas')}
        callback={handleShow}
        ref={ref}
      />

      <Offcanvas
        show={show}
        onHide={handleClose}
        placement={get(props.offcanvas, 'placement')}
        backdrop={get(props.offcanvas, 'backdrop')}
        scroll={get(props.offcanvas, 'scroll')}
      >
        {get(props.offcanvas, 'component') && createElement(get(props.offcanvas, 'component'))}
      </Offcanvas>
    </>
  )
})

// for debug purpose, otherwise component is named after the HOC
OffcanvasButton.displayName = 'OffcanvasButton'

implementPropTypes(OffcanvasButton, ButtonTypes, {
  id: T.string.isRequired,
  offcanvas: T.shape({
    placement: T.oneOf(['start', 'end', 'top', 'bottom']),
    backdrop: T.bool,
    scroll: T.bool,
    component: T.elementType
  }).isRequired
})

export {
  OffcanvasButton
}
