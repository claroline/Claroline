import React from 'react'

import {trans} from '#/main/app/intl'
import {ToolEditor} from '#/main/core/tool/editor/containers/main'

import {EditorArchives} from '#/main/core/tools/resources/editor/components/archives'

const ResourcesEditor = () =>
  <ToolEditor
    pages={[
      {
        name: 'archives',
        title: trans('archives'),
        component: EditorArchives,
        managerOnly: true
      }
    ]}
  />

export {
  ResourcesEditor
}
