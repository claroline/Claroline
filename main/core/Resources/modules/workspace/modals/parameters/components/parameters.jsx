import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {selectors} from '#/main/core/workspace/modals/parameters/store'
import {WorkspaceForm} from '#/main/core/workspace/components/form'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const ParametersModalComponent = props =>
  <Modal
    {...omit(props, 'workspace', 'saveEnabled', 'workspaceLoading', 'loadWorkspace', 'saveWorkspace')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={props.workspace.name}
    onEntering={() => props.workspaceLoading ? props.loadWorkspace(props.workspace) : null}
  >
    <WorkspaceForm
      name={selectors.STORE_NAME}
      modal={true}
    />

    <Button
      className="modal-btn btn btn-primary"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.saveWorkspace(props.workspace)
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModalComponent.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  workspaceLoading: T.bool.isRequired,
  saveWorkspace: T.func.isRequired,
  loadWorkspace: T.func.isRequired,
  fadeModal: T.func.isRequired
}

ParametersModalComponent.defaultProps = {
  workspaceLoading: true
}

const ParametersModal = connect(
  (state) => ({
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME))
  }),
  (dispatch) => ({
    loadWorkspace(workspace) {
      dispatch(formActions.resetForm(selectors.STORE_NAME, workspace))
    },
    saveWorkspace(workspace) {
      dispatch(formActions.saveForm(selectors.STORE_NAME, ['apiv2_workspace_update', {id: workspace.id}]))
    }
  })
)(ParametersModalComponent)

export {
  ParametersModal
}
