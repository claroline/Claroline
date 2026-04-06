import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {ResourceEditor} from '#/main/core/resource/editor'

import {selectors} from '#/plugin/project/resources/project/store/selectors'
import {ProjectEditorParameters} from '#/plugin/project/resources/project/editor/components/parameters'
import {ProjectEditorMilestones} from '#/plugin/project/resources/project/editor/components/milestones'
import {ProjectEditorCriteria} from '#/plugin/project/resources/project/editor/components/criteria'

const ProjectEditor = () => {
  const project = useSelector(selectors.project)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: project
      })}
      pages={[
        {
          name: 'parameters',
          title: trans('parameters'),
          component: ProjectEditorParameters
        }, {
          name: 'milestones',
          title: trans('milestones', {}, 'project'),
          component: ProjectEditorMilestones
        }, {
          name: 'criteria',
          title: trans('criteria', {}, 'project'),
          component: ProjectEditorCriteria
        }
      ]}
    />
  )
}

export {
  ProjectEditor
}
