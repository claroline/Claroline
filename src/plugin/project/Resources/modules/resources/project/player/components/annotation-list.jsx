import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {ContentHtml} from '#/main/app/content/components/html'

/**
 * Displays a list of trainer annotations for a submission, grouped by milestone.
 * Used in iterative mode to show ongoing feedback before the final correction.
 *
 * @param {Array}  annotations - List of annotation objects
 * @param {Array}  milestones  - List of milestone objects (for labels)
 */
const AnnotationList = ({annotations, milestones}) => {
  if (!annotations || annotations.length === 0) {
    return (
      <div className="text-muted fst-italic py-3">
        {trans('no_annotations_yet', {}, 'project')}
      </div>
    )
  }

  // Group annotations by milestone
  const grouped = {}
  annotations.forEach(annotation => {
    const milestoneId = annotation.milestone || 'general'
    if (!grouped[milestoneId]) {
      grouped[milestoneId] = []
    }
    grouped[milestoneId].push(annotation)
  })

  const getMilestoneTitle = (milestoneId) => {
    if ('general' === milestoneId) {
      return trans('general')
    }
    const milestone = milestones.find(m => m.id === milestoneId)
    return milestone ? milestone.title : milestoneId
  }

  return (
    <div className="annotation-list">
      {Object.keys(grouped).map(milestoneId => (
        <div key={milestoneId} className="mb-3">
          <h6 className="text-primary fw-bold mb-2">
            <span className="fa fa-fw fa-flag me-1" />
            {getMilestoneTitle(milestoneId)}
          </h6>

          {grouped[milestoneId].map((annotation, index) => (
            <div key={annotation.id || index} className="card mb-2">
              <div className="card-body py-2 px-3">
                <div className="d-flex justify-content-between align-items-center mb-1">
                  <small className="fw-bold">
                    {annotation.user
                      ? `${annotation.user.firstName} ${annotation.user.lastName}`
                      : trans('trainer', {}, 'project')
                    }
                  </small>
                  <small className="text-muted">
                    {displayDate(annotation.date, false, true)}
                  </small>
                </div>
                <ContentHtml>{annotation.content}</ContentHtml>
              </div>
            </div>
          ))}
        </div>
      ))}
    </div>
  )
}

AnnotationList.propTypes = {
  annotations: T.arrayOf(T.shape({
    id: T.string,
    content: T.string.isRequired,
    date: T.string,
    milestone: T.string,
    user: T.shape({
      firstName: T.string,
      lastName: T.string
    })
  })),
  milestones: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  }))
}

AnnotationList.defaultProps = {
  annotations: [],
  milestones: []
}

export {
  AnnotationList
}
