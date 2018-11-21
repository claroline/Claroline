import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {WorkspaceList} from '#/main/core/workspace/list/components/workspace-list'

// TODO : reuse workspace list configuration here

const Workspace = () =>
  <ListData
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
    definition={WorkspaceList.definition}
    card={WorkspaceList.card}
  />

export {
  Workspace
}
