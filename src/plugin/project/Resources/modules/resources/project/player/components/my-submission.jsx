import React, {useEffect, useState} from 'react'
import {useSelector, useDispatch} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ContentHtml} from '#/main/app/content/components/html'
import {ResourcePage} from '#/main/core/resource'
import {PageSection} from '#/main/app/page'

import {selectors} from '#/plugin/project/resources/project/store/selectors'
import {actions} from '#/plugin/project/resources/project/store/actions'
import {
  SUBMISSION_TYPE_FILE,
  SUBMISSION_TYPE_RICH_TEXT,
  SUBMISSION_TYPE_URL,
  SUBMISSION_TYPE_NONE,
  CORRECTION_STATUS_SUBMITTED,
  DEADLINE_TYPE_NONE
} from '#/plugin/project/resources/project/constants'
import {MilestoneStepper} from '#/plugin/project/resources/project/player/components/milestone-stepper'
import {AnnotationList} from '#/plugin/project/resources/project/player/components/annotation-list'
import {DeadlineCountdown} from '#/plugin/project/resources/project/player/components/deadline-countdown'

const MySubmission = () => {
  const path = useSelector(resourceSelectors.path)
  const project = useSelector(selectors.project)
  const mySubmissions = useSelector(selectors.mySubmissions)
  const myCorrections = useSelector(selectors.myCorrections)
  const myDeadline = useSelector(selectors.myDeadline)
  const milestones = useSelector(selectors.milestones)
  const dispatch = useDispatch()

  const hasSubmitted = mySubmissions && mySubmissions.length > 0
  const publishedCorrections = myCorrections.filter(c => CORRECTION_STATUS_SUBMITTED === c.status)
  const hasMilestones = milestones && milestones.length > 0

  // Determine current step based on which milestones have submissions
  const completedMilestoneIds = mySubmissions
    .filter(s => s.milestone)
    .map(s => s.milestone)
  const currentStep = hasMilestones
    ? milestones.findIndex(m => !completedMilestoneIds.includes(m.id))
    : 0

  // Collect all annotations across all submissions
  const allAnnotations = mySubmissions
    .flatMap(s => s.annotations || [])

  // Fetch deadline on mount
  useEffect(() => {
    if (project && DEADLINE_TYPE_NONE !== project.deadlineType) {
      dispatch(actions.fetchMyDeadline(project.id))
    }
  }, [project.id])

  return (
    <ResourcePage
      title={trans('my_submission', {}, 'project')}
    >
      {/* Deadline countdown */}
      {DEADLINE_TYPE_NONE !== project.deadlineType && myDeadline &&
        <div className="mb-3">
          <DeadlineCountdown deadline={myDeadline} />
        </div>
      }

      {/* Milestone stepper for iterative mode */}
      {hasMilestones &&
        <PageSection title={trans('progress', {}, 'project')}>
          <MilestoneStepper
            milestones={milestones}
            currentStep={currentStep >= 0 ? currentStep : milestones.length}
            completedSteps={completedMilestoneIds}
          />
        </PageSection>
      }

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

      {/* Trainer annotations (iterative mode) */}
      {hasMilestones && allAnnotations.length > 0 &&
        <PageSection title={trans('trainer_annotations', {}, 'project')}>
          <AnnotationList
            annotations={allAnnotations}
            milestones={milestones}
          />
        </PageSection>
      }
    </ResourcePage>
  )
}

export {
  MySubmission
}
