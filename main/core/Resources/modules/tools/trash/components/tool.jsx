import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ResourceList} from '#/main/core/resource/data/components/resource-list'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {actions} from '#/main/core/tools/trash/store/actions'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'

const TrashToolComponent = props =>
  <ToolPage
    subtitle={trans('trash')}
    disabled={false}
  >
    <ListData
      name="resources"
      fetch={{
        url: ['apiv2_resource_workspace_removed_list', {workspace: props.workspace.uuid}],
        autoload: true
      }}
      delete={{
        url: ['claro_resource_collection_action', {action: 'hard_delete'}]
      }}
      primaryAction={ResourceList.open}
      actions={(rows) => [
        {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-undo-alt',
          label: trans('restore', {}, 'actions'),
          callback: () => props.restore(rows),
          dangerous: false
        }
      ]}
      definition={ResourceList.definition}
      card={ResourceList.card}
    />
  </ToolPage>
TrashToolComponent.propTypes = {
  workspace: T.shape(
    ResourceNodeTypes.propTypes
  ),
  restore: T.func.isRequired
}

const TrashTool = connect(
  state => ({
    workspace: state.workspace
  }),
  dispatch => ({
    restore(resourceNodes) {
      dispatch(actions.restore(resourceNodes))
    }
  })
)(TrashToolComponent)

export {
  TrashTool
}
