import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans}  from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'

const RightsModal = props =>
  <Modal
    {...omit(props, 'toolName', 'saveEnabled', 'save')}
    icon="fa fa-fw fa-lock"
    title={trans('rights')}
    subtitle={props.toolName}
    onEntering={() => true}
  >

    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save()
        props.fadeModal()
      }}
    />
  </Modal>

RightsModal.propTypes = {
  toolName: T.string.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  RightsModal
}
