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
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {trans} from '#/main/core/translation'

import {SessionEventForm} from '#/plugin/cursus/administration/cursus/session-event/components/form'

const SessionEventFormModalComponent = props =>
  <Modal
    {...omit(props, 'saveEnabled', 'saveSessionEvent')}
    icon="fa fa-fw fa-cog"
    title={trans('session_event', {}, 'cursus')}
  >
    <SessionEventForm
      name="events.current"
    />

    <Button
      className="modal-btn btn btn-primary"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.saveSessionEvent()
        props.fadeModal()
      }}
    />
  </Modal>

SessionEventFormModalComponent.propTypes = {
  saveEnabled: T.bool.isRequired,
  saveSessionEvent: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const SessionEventFormModal = connect(
  (state) => ({
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'events.current'))
  }),
  (dispatch) => ({
    saveSessionEvent() {
      dispatch(formActions.saveForm('events.current', ['apiv2_cursus_session_event_create']))
    }
  })
)(SessionEventFormModalComponent)

export {
  SessionEventFormModal
}
