import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {
  DEADLINE_TYPES,
  DEADLINE_TYPE_FIXED,
  DEADLINE_TYPE_RELATIVE
} from '#/plugin/project/resources/project/constants'

const ProjectEditorMilestones = () => {
  const project = useSelector(editorSelectors.resource)

  return (
    <EditorPage
      title={trans('milestones', {}, 'project')}
      dataPart="resource"
      definition={[
        {
          title: trans('milestones', {}, 'project'),
          primary: true,
          fields: [
            {
              name: 'milestones',
              type: 'collection',
              label: trans('milestones', {}, 'project'),
              options: {
                type: 'milestone',
                placeholder: trans('no_milestone', {}, 'project'),
                button: trans('add_milestone', {}, 'project')
              }
            }
          ]
        }
      ]}
    />
  )
}

export {
  ProjectEditorMilestones
}
