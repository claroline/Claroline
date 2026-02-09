import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {ResourceEditor} from '#/main/core/resource/editor'

const ShortcutEditorTarget = () =>
  <EditorPage
    title={trans('target_resource', {}, 'link')}
    help={trans('target_resource_help', {}, 'link')}
    dataPart="resource"
    definition={[
      {
        name: 'general',
        title: trans('general'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'target',
            type: 'resource',
            label: trans('resource'),
            hideLabel: true,
            required: true
          }
        ]
      }
    ]}
  />

const ShortcutEditor = () => {
  return (
    <ResourceEditor
      defaultPage="target"
      pages={[
        {
          name: 'target',
          title: trans('target_resource', {}, 'link'),
          component: ShortcutEditorTarget
        }
      ]}
    />
  )
}

export {
  ShortcutEditor
}
