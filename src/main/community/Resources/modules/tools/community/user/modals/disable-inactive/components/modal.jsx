import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {DataInput} from '#/main/app/data/components/input'
import {Alert} from '#/main/app/components/alert'

const DisableInactiveModal = (props) => {
  const [lastActivity, setLastActivity] = useState(null)

  return (
    <Modal
      {...omit(props, 'disableInactive')}
      icon="fa fa-fw fa-user-clock"
      title={trans('disable_inactive_users', {}, 'community')}
    >
      <div className="modal-body">
        <Alert type="danger">{trans('disable_inactive_users_help', {}, 'community')}</Alert>

        <DataInput
          id="lastActivity"
          type="date"
          label={trans('last_activity')}
          value={lastActivity}
          onChange={setLastActivity}
          required={true}
        />
      </div>

      <Button
        className="modal-btn"
        variant="btn"
        size="lg"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('disable', {}, 'actions')}
        disabled={isEmpty(lastActivity)}
        callback={() => {
          props.disableInactive(lastActivity)
          props.fadeModal()
        }}
        dangerous={true}
      />
    </Modal>
  )
}

DisableInactiveModal.propTypes = {
  disableInactive: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  DisableInactiveModal
}
