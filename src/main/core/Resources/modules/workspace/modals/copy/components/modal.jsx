import React, {Fragment, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {ConfirmModal} from '#/main/app/modals/confirm/components/modal'
import {Checkbox} from '#/main/app/input/components/checkbox'

import {WorkspaceCard} from '#/main/core/workspace/components/card'

const CopyModal = props => {
  const [hasModel, setHasModel] = useState(false)

  return (
    <ConfirmModal
      {...omit(props, 'formData', 'saveEnabled', 'save', 'reset')}
      icon="fa fa-fw fa-clone"
      title={transChoice('copy_confirm_title', props.workspaces.length, {count: props.workspaces.length}, 'workspace')}
      subtitle={1 === props.workspaces.length ? props.workspaces[0].name : transChoice('count_elements', props.workspaces.length, {count: props.workspaces.length})}
      question={transChoice('copy_confirm_message', props.workspaces.length, {count: props.workspaces.length}, 'workspace')}
      additional={
        <Fragment>
          <div className="modal-body">
            {props.workspaces.map(workspace =>
              <WorkspaceCard
                key={workspace.id}
                orientation="row"
                size="xs"
                data={workspace}
              />
            )}
          </div>

          <div className="modal-footer">
            <Checkbox
              id="copy-as-model"
              label={trans('copy_as_model', {}, 'workspace')}
              onChange={setHasModel}
              checked={hasModel}
              inline={true}
            />
          </div>
        </Fragment>
      }
      confirmAction={{
        type: ASYNC_BUTTON,
        label: trans('copy', {}, 'actions'),
        request: {
          url: url(['apiv2_workspace_copy_bulk'], {
            ids: props.workspaces.map(workspace => workspace.id),
            model: hasModel
          }),
          request: {
            method: 'GET'
          },
          success: (response) => {
            if (props.onCopy) {
              props.onCopy(response)
            }
          }
        }
      }}
    />
  )
}

CopyModal.propTypes = {
  workspaces: T.arrayOf(T.object).isRequired,
  onCopy: T.func
}

export {
  CopyModal
}
