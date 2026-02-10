import React, {useEffect} from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {MODAL_SECURITY} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as modalActions} from '#/main/app/overlays/modal'

import {actions} from '#/main/app/context/store'
import {ContextError} from '#/main/app/context/components/error'

const DesktopError = (props) => {
  const dispatch = useDispatch()
  const authenticated = useSelector(securitySelectors.isAuthenticated)

  useEffect(() => {
    if (!authenticated) {
      dispatch(modalActions.showModal(MODAL_SECURITY, {
        onLogin: () => dispatch(actions.reload()),
        onRegister: () => dispatch(actions.reload())
      }))
    }
  }, [authenticated])

  return (
    <ContextError {...props} />
  )
}

export {
  DesktopError
}
