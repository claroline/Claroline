import React, {useCallback} from 'react'
import {useSelector, useDispatch} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ContentHtml} from '#/main/app/content/components/html'
import {ToolPage} from '#/main/app/page/components/tool-page'
import {PageSection} from '#/main/app/page/components/section'
import {Alert} from '#/main/app/alert/components/alert'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'

import {selectors} from '#/plugin/project/resources/project/store/selectors'
import {
  SUBMISSION_TYPE_FILE,
  SUBMISSION_TYPE_RICH_TEXT,
  SUBMISSION_TYPE_URL,
  SUBMISSION_TYPE_NONE,
  CORRECTION_STATUS_SUBMITTED
} from '#/plugin/project/resources/project/constants'

const MySubmission = () => {
  const path = useSelector(resourceSelectors.path)
  const project = useSelector(selectors.project)
  const mySubmissions = useSelector(selectors.mySubmissions)
  const myCorrections = useSelector(selectors.myCorrections)

  const hasSubmitted = mySubmissions && mySubmissions.length > 0
  const publishedCorrections = myCorrections.filter(c => CORRECTION_STATUS_SUBMITTED === c.status)
  const latestSubmission = hasSubmitted ? mySubmissions[mySubmissions.length - 1] : null

  return (
    <ToolPage
      title={trans('my_submission', {}, 'project')}
    >
      {project.instruction &&
        <PageSection title={trans('instructions', {}, 'project')}>
          <ContentHtml>{project.instruction}</ContentHtml>
        </PageSection>
      }

      {project.templateFile &&
        <PageSection title={trans('template', {}, 'project')}>
          <a href={project.templateFile.url} download={project.templateFile.name}>
            {project.templateFile.name}
          </a>
        </PageSection>
      }

      {!hasSubmitted && SUBMISSION_TYPE_NONE !== project.submissionType &&
        <PageSection title={trans('submit_work', {}, 'project')}>
          <Alert type="info">
            {trans('no_submission_yet', {}, 'project')}
          </Alert>
        </PageSection>
      }

      {hasSubmitted &&
        <PageSection title={trans('my_submissions', {}, 'project')}>
          {mySubmissions.map((submission, index) => (
            <div key={submission.id || index} className="card mb-3">
              <div className="card-body">
                {submission.milestone &&
                  <span className="badge bg-primary me-2">{submission.milestone.title}</span>
                }
                <small className="text-muted">
                  {trans('submitted_on', {date: displayDate(submission.submittedDate, false, true)}, 'project')}
                </small>

                {SUBMISSION_TYPE_FILE === submission.contentType && submission.fileData &&
                  <div className="mt-2">
                    <a href={submission.fileData.url} download={submission.fileData.name}>
                      <span className="fa fa-fw fa-download me-1" />
                      {submission.fileData.name}
                    </a>
                  </div>
                }

                {SUBMISSION_TYPE_RICH_TEXT === submission.contentType && submission.textContent &&
                  <div className="mt-2">
                    <ContentHtml>{submission.textContent}</ContentHtml>
                  </div>
                }

                {SUBMISSION_TYPE_URL === submission.contentType && submission.urlContent &&
                  <div className="mt-2">
                    <a href={submission.urlContent} target="_blank" rel="noopener noreferrer">
                      {submission.urlContent}
                    </a>
                  </div>
                }
              </div>
            </div>
          ))}
        </PageSection>
      }

      {publishedCorrections.length > 0 &&
        <PageSection title={trans('my_corrections', {}, 'project')}>
          {publishedCorrections.map((correction, index) => (
            <div key={correction.id || index} className="card mb-3">
              <div className="card-body">
                {null !== correction.score && undefined !== correction.score &&
                  <div className="mb-2">
                    <ScoreGauge
                      userScore={correction.score}
                      maxScore={project.scoreMax}
                    />
                  </div>
                }

                {correction.comment &&
                  <div className="mt-2">
                    <ContentHtml>{correction.comment}</ContentHtml>
                  </div>
                }

                {correction.criteriaScores && correction.criteriaScores.length > 0 &&
                  <div className="mt-2">
                    <h6>{trans('criteria_details', {}, 'project')}</h6>
                    <ul className="list-group">
                      {correction.criteriaScores.map((cs, csIndex) => (
                        <li key={csIndex} className="list-group-item d-flex justify-content-between align-items-center">
                          <span>{cs.criteria ? cs.criteria.label : ''}</span>
                          <span className="badge bg-primary">{cs.score} / {cs.criteria ? cs.criteria.scoreMax : ''}</span>
                        </li>
                      ))}
                    </ul>
                  </div>
                }
              </div>
            </div>
          ))}
        </PageSection>
      }
    </ToolPage>
  )
}

export {
  MySubmission
}
