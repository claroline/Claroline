import React from 'react'

import {trans} from '#/main/app/intl'
import {ToolEditor} from '#/main/core/tool/editor/containers/main'

import {EditorArchives} from '#/main/core/tools/workspaces/editor/components/archives'
import {EditorModels} from '#/main/core/tools/workspaces/editor/components/models'

const WorkspacesEditor = () =>
  <ToolEditor
    pages={[
      {
        name: 'models',
        title: trans('models'),
        help: trans('Retrouvez et gérez tous les modèles d\'espaces.'),
        component: EditorModels
      }, {
        name: 'archives',
        title: trans('archives'),
        help: trans('Retrouvez et gérez tous les espaces archivés.'),
        component: EditorArchives,
        managerOnly: true
      }
    ]}
  />

export {
  WorkspacesEditor
}
