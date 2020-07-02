import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {SessionForm} from '#/plugin/cursus/administration/cursus/session/components/form'

const SessionFormModalComponent = props =>
  <Modal
    {...omit(props, 'saveEnabled', 'saveSession')}
    icon="fa fa-fw fa-cog"
    title={trans('session', {}, 'cursus')}
  >
    <SessionForm
      name={selectors.STORE_NAME + '.sessions.current'}
    />

    <Button
      className="modal-btn btn btn-primary"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.saveSession()
        props.fadeModal()
      }}
    />
  </Modal>

SessionFormModalComponent.propTypes = {
  saveEnabled: T.bool.isRequired,
  saveSession: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const SessionFormModal = connect(
  (state) => ({
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME + '.sessions.current'))
  }),
  (dispatch) => ({
    saveSession() {
      dispatch(formActions.saveForm(selectors.STORE_NAME + '.sessions.current', ['apiv2_cursus_session_create']))
    }
  })
)(SessionFormModalComponent)

export {
  SessionFormModal
}
