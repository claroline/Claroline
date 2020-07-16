import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/cursus/administration/modals/session-form/store'
import {SessionForm} from '#/plugin/cursus/administration/cursus/session/components/form'

const SessionFormModal = props =>
  <Modal
    {...omit(props, 'session', 'course', 'saveEnabled', 'loadSession', 'saveSession', 'onSave')}
    icon="fa fa-fw fa-plus"
    title={trans('session', {}, 'cursus')}
    onEntering={() => props.loadSession(props.session, props.course)}
  >
    <SessionForm
      name={selectors.STORE_NAME}
    >
      <Button
        className="modal-btn btn"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        callback={() => props.saveSession(props.session ? props.session.id : null, (data) => {
          props.onSave(data)
          props.fadeModal()
        })}
      />
    </SessionForm>
  </Modal>

SessionFormModal.propTypes = {
  session: T.shape({
    id: T.string.isRequired
  }),
  course: T.shape({

  }),
  saveEnabled: T.bool.isRequired,
  loadSession: T.func.isRequired,
  saveSession: T.func.isRequired,
  onSave: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  SessionFormModal
}
