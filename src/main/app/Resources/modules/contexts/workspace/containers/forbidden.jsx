import React from 'react'
import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as configSelectors} from '#/main/app/config/store'
import {actions as contextActions, selectors as contextSelectors} from '#/main/app/context/store'

import {WorkspaceForbidden as WorkspaceForbiddenComponent} from '#/main/app/contexts/workspace/components/forbidden'
import {actions} from '#/main/app/contexts/workspace/store'

const WorkspaceForbidden = connect(
  (state) => ({
    errors: contextSelectors.accessErrors(state),
    workspace: contextSelectors.data(state),
    managed: contextSelectors.managed(state),
    currentUser: securitySelectors.currentUser(state),
    platformSelfRegistration: configSelectors.param(state, 'selfRegistration')
  }),
  (dispatch) => ({
    dismiss() {
      dispatch(contextActions.dismissRestrictions())
    },
    checkAccessCode(contextData, code) {
      dispatch(actions.checkAccessCode(contextData, code))
    },
    selfRegister(contextData) {
      dispatch(actions.selfRegister(contextData))
    }
  })
)(WorkspaceForbiddenComponent)

export {
  WorkspaceForbidden
}
