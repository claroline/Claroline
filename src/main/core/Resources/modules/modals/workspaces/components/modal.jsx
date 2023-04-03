import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {WorkspaceList} from '#/main/core/workspace/components/list'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {selectors} from '#/main/core/modals/workspaces/store'

const WorkspacesModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'reset')}
      icon="fa fa-fw fa-book"
      className="data-picker-modal"
      bsSize="lg"
      onExited={props.reset}
    >
      <WorkspaceList
        name={selectors.STORE_NAME}
        url={props.url}
        primaryAction={undefined}
        actions={undefined}
      />

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

WorkspacesModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(WorkspaceTypes.propTypes)).isRequired,
  reset: T.func.isRequired
}

WorkspacesModal.defaultProps = {
  url: ['apiv2_workspace_list_managed'],
  title: trans('workspaces')
}

export {
  WorkspacesModal
}
