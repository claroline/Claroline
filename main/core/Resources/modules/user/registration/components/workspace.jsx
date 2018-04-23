import React from 'react'

import {trans} from '#/main/core/translation'
import {constants as listConst} from '#/main/core/data/list/constants'

import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {WorkspaceList} from '#/main/core/administration/workspace/workspace/components/workspace-list.jsx'

/**
 * @constructor
 */
const Workspace = () =>
  <DataListContainer
    name="workspaces"
    primaryAction={WorkspaceList.open}
    fetch={{
      url: ['apiv2_workspace_list_registerable'],
      autoload: true
    }}
    display={{
      current: listConst.DISPLAY_TILES_SM,
      available: Object.keys(listConst.DISPLAY_MODES)
    }}
    definition={[
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'code',
        label: trans('code'),
        displayed: true
      }
    ]}
    card={WorkspaceList.card}
  />

export {
  Workspace
}
