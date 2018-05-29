import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {RoleCard} from '#/main/core/user/data/components/role-card'

const ImpersonationModal = props =>
  <Modal
    {...omit(props, 'workspace')}
    icon="fa fa-fw fa-user-secret"
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
