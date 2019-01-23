import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ResourceList} from '#/main/core/resource/data/components/resource-list'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const TrashTool = props =>
  <ToolPage
    subtitle={trans('trash')}
  >
    <ListData
      name="resources"
      fetch={{
        url: ['apiv2_resource_workspace_removed_list', {
          workspace: props.workspace.uuid
        }],
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
      definition={[
        {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true,
          primary: true
        },
        {
          type: 'date',
          name: 'meta.updated',
          label: trans('last_modification'),
          displayed: true
        },
        {
          type: 'string',
          filterable: false,
          displayed: true,
          label: trans('type'),
          name: trans('meta.type', {}, 'resource')
        }
      ]}
      card={ResourceList.card}
    />
  </ToolPage>

TrashTool.propTypes = {
  workspace: T.shape(
    ResourceNodeTypes.propTypes
  ),
  restore: T.func.isRequired
}

export {
  TrashTool
}
