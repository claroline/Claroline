import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentLoader} from '#/main/app/content/components/loader'

import {WorkspaceForm} from '#/main/core/workspace/components/form'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {selectors} from '#/main/core/workspace/modals/parameters/store'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'workspaceId', 'workspace', 'saveEnabled', 'reset', 'loadWorkspace', 'saveWorkspace', 'onSave')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={get(props.workspace, 'name')}
    onEntering={() => props.loadWorkspace(props.workspaceId)}
    onExiting={() => props.reset()}
  >
    {isEmpty(props.workspace) &&
      <ContentLoader
        size="lg"
        description={trans('loading', {}, 'workspace')}
      />
    }

    {!isEmpty(props.workspace) &&
      <WorkspaceForm name={selectors.STORE_NAME}>
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
      </WorkspaceForm>
    }
  </Modal>

ParametersModal.propTypes = {
  workspaceId: T.string.isRequired,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  onSave: T.func,
  saveEnabled: T.bool.isRequired,
  saveWorkspace: T.func.isRequired,
  loadWorkspace: T.func.isRequired,
  reset: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
