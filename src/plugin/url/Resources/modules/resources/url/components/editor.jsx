import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {ResourceEditor, selectors as editorSelectors} from '#/main/core/resource/editor'

import {UrlForm} from '#/plugin/url/components/form'
import {selectors} from '#/plugin/url/resources/url/store'

const UrlEditorTarget = () =>
  <EditorPage
    title={trans('url')}
  >
    <UrlForm
      name={editorSelectors.STORE_NAME}
      dataPart="resource"
      updateProp={() => true}
      embedded={true}
    />
  </EditorPage>

const UrlEditor = () => {
  const url = useSelector(selectors.url)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: url
      })}
      pages={[
        {
          name: 'url',
          title: trans('url'),
          component: UrlEditorTarget
        }
      ]}
    />
  )
}

export {
  UrlEditor
}
