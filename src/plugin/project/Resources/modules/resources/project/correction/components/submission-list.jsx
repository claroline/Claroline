import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/project/resources/project/store/selectors'

const SubmissionList = () => {
  const path = useSelector(resourceSelectors.path)
  const project = useSelector(selectors.project)

  return (
    <ListData
      name={selectors.STORE_NAME + '.submissions'}
      fetch={{
        url: ['apiv2_project_submissions', {id: project.id}],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `${path}/correction/${row.id}`
      })}
      definition={[
        {
          name: 'user.lastName',
          type: 'string',
          label: trans('last_name'),
          displayed: true,
          primary: true
        }, {
          name: 'user.firstName',
          type: 'string',
          label: trans('first_name'),
          displayed: true
        }, {
          name: 'submittedDate',
          type: 'date',
          label: trans('submission_date', {}, 'project'),
          displayed: true,
          options: {
            time: true
          }
        }, {
          name: 'contentType',
          type: 'choice',
          label: trans('type'),
          displayed: true
        }, {
          name: 'milestone.title',
          type: 'string',
          label: trans('milestone', {}, 'project'),
          displayed: true
        }, {
          name: 'corrected',
          type: 'boolean',
          label: trans('corrected', {}, 'project'),
          displayed: true
        }
      ]}
    />
  )
}

export {
  SubmissionList
}
