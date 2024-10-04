import React from 'react'

import {trans} from '#/main/app/intl'
import {ToolEditor} from '#/main/core/tool'

import {BadgesEditorActions} from '#/plugin/open-badge/tools/badges/editor/components/actions'
import {BadgesEditorArchives} from '#/plugin/open-badge/tools/badges/editor/components/archives'

const BadgesEditor = () =>
  <ToolEditor
    actionsPage={BadgesEditorActions}
    pages={[
      {
        name: 'archives',
        title: trans('archives'),
        help: trans('Retrouvez et gérez tous les badges archivés.'),
        component: BadgesEditorArchives,
        managerOnly: true
      }
    ]}
  />

export {
  BadgesEditor
}
