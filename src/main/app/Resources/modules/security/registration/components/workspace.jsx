import React from 'react'

import {constants as listConst} from '#/main/app/content/list/constants'

import {selectors} from '#/main/app/security/registration/store/selectors'
import {WorkspaceList} from '#/main/core/workspace/components/list'

const Workspace = () =>
  <WorkspaceList
    name={`${selectors.STORE_NAME}.workspaces`}
    url={['apiv2_workspace_list_registerable']}
    display={{
      current: listConst.DISPLAY_TILES_SM,
      available: Object.keys(listConst.DISPLAY_MODES)
    }}
  />

export {
  Workspace
}
