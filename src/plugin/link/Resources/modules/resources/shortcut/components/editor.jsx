import React from 'react'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/link/resources/shortcut/store'
import {ResourceEditor} from '#/main/core/resource/editor'
import {useSelector} from 'react-redux'
import {EditorPage} from '#/main/app/editor'

const ShortcutEditor = () => {
  const shortcut = useSelector(selectors.shortcut)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: shortcut
      })}
      defaultPage="target"
      pages={[
        {
          name: 'target',
          title: trans('target_resource', {}, 'link'),
          primary: true,
          render: () =>(
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
                      required: true,
                      options: {
                        embedded: true,
                        showHeader: true,
                      }
                    }
                  ]
                }
              ]}
            />
          )
        }
      ]}
    />
  )
}

export {
  ShortcutEditor
}
