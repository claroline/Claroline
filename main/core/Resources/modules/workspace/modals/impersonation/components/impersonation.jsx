import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {RoleCard} from '#/main/core/user/data/components/role-card'

const ImpersonationModal = props =>
  <Modal
    {...omit(props, 'workspace')}
    icon="fa fa-fw fa-mask"
    title={trans('view-as', {}, 'actions')}
    subtitle={props.workspace.name}
  >
    <div className="modal-body">
      {props.workspace.roles.map(role =>
        <RoleCard
          key={role.name}
          className="component-container"
          data={role}
          primaryAction={{
            type: 'url',
            label: trans('view-as', {}, 'actions'),
            //TODO: WORKSPACE OPEN URL CHANGE
            target: url(['claro_workspace_open', {workspaceId: props.workspace.id}], {view_as: role.name})
          }}
        />
      )}
    </div>
  </Modal>

ImpersonationModal.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  fadeModal: T.func.isRequired
}

export {
  ImpersonationModal
}
