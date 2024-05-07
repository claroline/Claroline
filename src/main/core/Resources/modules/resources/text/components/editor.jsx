import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {ResourceEditor} from '#/main/core/resource'

import {selectors} from '#/main/core/resources/text/store'

const TextContent = () => {
  const availablePlaceholders = useSelector(selectors.availablePlaceholders)

  return (
    <EditorPage
      title={trans('content')}
      definition={[
        {
          title: trans('general'),
          fields: [
            {
              name: 'resource.raw',
              type: 'html',
              label: trans('content'),
              hideLabel: true,
              options: {
                minimal: false,
                config: {
                  plugins: ['placeholders'],
                  placeholders: availablePlaceholders
                }
              }
            }
          ]
        }
      ]}
    />
  )
}

const TextEditor = () => {
  const originalText = useSelector(selectors.text)

  return (
    <ResourceEditor
      additionalData={() => ({resource: originalText})}
      defaultPage="content"
      pages={[{
        name: 'content',
        title: trans('content'),
        component: TextContent
      }]}
    />
  )
}

export {
  TextEditor
}
