import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

const ProjectEditorCriteria = () => {
  const project = useSelector(editorSelectors.resource)

  return (
    <EditorPage
      title={trans('criteria', {}, 'project')}
      dataPart="resource"
      definition={[
        {
          title: trans('evaluation_criteria', {}, 'project'),
          primary: true,
          fields: [
            {
              name: 'criteria',
              type: 'collection',
              label: trans('criteria', {}, 'project'),
              options: {
                type: 'criterion',
                placeholder: trans('no_criterion', {}, 'project'),
                button: trans('add_criterion', {}, 'project')
              }
            }
          ]
        }
      ]}
    />
  )
}

export {
  ProjectEditorCriteria
}
