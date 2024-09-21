import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'

import {WorkspaceList} from '#/main/core/workspace/components/list'
import {selectors} from '#/main/core/tools/workspaces/editor/store/selectors'

const EditorArchives = () =>
  <EditorPage
    title={trans('archives')}
    help={trans('Retrouvez et gérez tous les espaces archivés.')}
    managerOnly={true}
  >
    <WorkspaceList
      url={['apiv2_workspace_list_archive']}
      name={selectors.ARCHIVES_LIST_NAME}
      customDefinition={[
        {
          name: 'meta.model',
          label: trans('model'),
          type: 'boolean',
          alias: 'model'
        }
      ]}
    />
  </EditorPage>

export {
  EditorArchives
}
