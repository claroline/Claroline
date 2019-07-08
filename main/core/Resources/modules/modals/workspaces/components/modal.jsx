import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/modals/workspaces/store'
import {WorkspaceList} from '#/main/core/workspace/list/components/workspace-list'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'

const WorkspacesModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'resetSelect')}
      icon="fa fa-fw fa-books"
      className="data-picker-modal"
      bsSize="lg"
      onExiting={props.resetSelect}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: props.url,
          autoload: true
        }}
        definition={WorkspaceList.definition}
        card={WorkspaceList.card}
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
  selected: T.arrayOf(T.shape(WorkspaceType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

WorkspacesModal.defaultProps = {
  url: ['apiv2_workspace_list_managed'],
  title: trans('workspaces')
}

export {
  WorkspacesModal
}
