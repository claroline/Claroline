import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {LINK_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ContentHtml} from '#/main/app/content/components/html'

import {selectors} from '#/plugin/project/resources/project/store/selectors'
import {
  SUBMISSION_TYPES,
  DEADLINE_TYPE_FIXED,
  DEADLINE_TYPE_RELATIVE,
  DEADLINE_TYPE_NONE
} from '#/plugin/project/resources/project/constants'

const Overview = () => {
  const resourceNode = useSelector(resourceSelectors.resourceNode)
  const path = useSelector(resourceSelectors.path)
  const project = useSelector(selectors.project)
  const mySubmissions = useSelector(selectors.mySubmissions)
  const canEdit = hasPermission('edit', resourceNode)

  const hasSubmitted = mySubmissions && mySubmissions.length > 0

  return (
    <ResourceOverview
      contentText={project.instruction}
      display={{
        score: true,
        scoreMax: project.scoreMax
      }}
      details={[
        [
          trans('submission_type', {}, 'project'),
          SUBMISSION_TYPES[project.submissionType] || project.submissionType
        ],
        project.estimatedDuration && [
          trans('estimated_duration', {}, 'project'),
          transChoice('duration_minutes', project.estimatedDuration, {count: project.estimatedDuration}, 'project')
        ],
        DEADLINE_TYPE_FIXED === project.deadlineType && project.deadlineDate && [
          trans('deadline', {}, 'project'),
          displayDate(project.deadlineDate, false, true)
        ],
        DEADLINE_TYPE_RELATIVE === project.deadlineType && project.deadlineDays && [
          trans('deadline', {}, 'project'),
          transChoice('deadline_days_label', project.deadlineDays, {count: project.deadlineDays}, 'project')
        ],
        DEADLINE_TYPE_NONE === project.deadlineType && [
          trans('deadline', {}, 'project'),
          trans('none')
        ],
        hasSubmitted && [
          trans('submission_status', {}, 'project'),
          trans('submitted')
        ]
      ].filter(value => !!value)}

      primaryAction="participate"
      actions={[
        {
          name: 'participate',
          type: LINK_BUTTON,
          label: trans(hasSubmitted ? 'show_my_submission' : 'submit_work', {}, 'project'),
          target: `${path}/my/submission`,
          primary: !hasSubmitted,
          displayed: !canEdit
        }, {
          name: 'manage',
          type: LINK_BUTTON,
          label: trans('manage_submissions', {}, 'project'),
          target: `${path}/submissions`,
          primary: true,
          displayed: canEdit
        }
      ]}
    />
  )
}

export {
  Overview
}
