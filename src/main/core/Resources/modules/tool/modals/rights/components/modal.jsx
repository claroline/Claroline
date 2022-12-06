import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans}  from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {ContentRights} from '#/main/app/content/components/rights'

const RightsModal = props =>
  <Modal
    {...omit(props, 'toolName', 'currentContext', 'rights', 'loadRights', 'updateRights', 'saveEnabled', 'save', 'onSave')}
    icon="fa fa-fw fa-lock"
    title={trans('rights')}
    subtitle={trans(props.toolName, {}, 'tools')}
    onEntering={() => props.loadRights(props.toolName, props.currentContext)}
  >
    <ContentRights
      workspace={get(props.currentContext, 'data')}
      rights={props.rights}
      updateRights={props.updateRights}
    />

    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save(props.toolName, props.currentContext, props.onSave)
        props.fadeModal()
      }}
    />
  </Modal>

RightsModal.propTypes = {
  toolName: T.string.isRequired,
  currentContext: T.object.isRequired,
  rights: T.arrayOf(T.shape({
    name: T.string.isRequired,
    translationKey: T.string.isRequired,
    permissions: T.object.isRequired
  })),
  loadRights: T.func.isRequired,
  updateRights: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  onSave: T.func,
  fadeModal: T.func.isRequired
}

export {
  RightsModal
}
