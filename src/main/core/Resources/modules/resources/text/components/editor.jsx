import React, {useEffect} from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {actions as formActions} from '#/main/app/content/form/store'
import {ResourceEditor, selectors as resourceSelectors} from '#/main/core/resource'

import {selectors} from '#/main/core/resources/text/store'
import {FormContent} from '#/main/app/content/form/containers/content'

const TextContent = () => {
  const availablePlaceholders = useSelector(selectors.availablePlaceholders)

  return (
    <FormContent
      name={resourceSelectors.EDITOR_NAME}
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
  const dispatch = useDispatch()
  const originalText = useSelector(selectors.text)

  // load text resource in editor
  useEffect(() => {
    dispatch(formActions.load(resourceSelectors.EDITOR_NAME, {resource: originalText}))
  }, [originalText.id])

  return (
    <ResourceEditor
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
