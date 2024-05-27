import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {ResourceEditor} from '#/main/core/resource/editor'

import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {DropzoneEditorParameters} from '#/plugin/drop-zone/resources/dropzone/editor/components/parameters'
import {DropzoneEditorDrop} from '#/plugin/drop-zone/resources/dropzone/editor/components/drop'
import {DropzoneEditorCorrection} from '#/plugin/drop-zone/resources/dropzone/editor/components/correction'

const DropzoneEditor = props => {
  const dropzone = useSelector(selectors.dropzone)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: dropzone
      })}
      pages={[
        {
          name: 'parameters',
          title: trans('parameters'),
          component: DropzoneEditorParameters
        }, {
          name: 'drop',
          title: trans('drop', {}, 'dropzone'),
          component: DropzoneEditorDrop
        }, {
          name: 'correction',
          title: trans('correction', {}, 'dropzone'),
          component: DropzoneEditorCorrection
        }
      ]}
    />
  )
}

export {
  DropzoneEditor
}
