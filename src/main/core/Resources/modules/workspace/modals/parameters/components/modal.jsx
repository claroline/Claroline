import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {WorkspaceForm} from '#/main/core/workspace/components/form'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {selectors} from '#/main/core/workspace/modals/parameters/store'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'workspace', 'saveEnabled', 'loadWorkspace', 'saveWorkspace', 'onSave')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={props.workspace.name}
    onEntering={() => props.loadWorkspace(props.workspace)}
  >
    <WorkspaceForm
      name={selectors.STORE_NAME}
    />

    <Button
      className="modal-btn btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.saveWorkspace(props.workspace, props.onSave)
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModal.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  onSave: T.func,
  saveEnabled: T.bool.isRequired,
  saveWorkspace: T.func.isRequired,
  loadWorkspace: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
