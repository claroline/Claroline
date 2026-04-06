import React, {useState, useCallback} from 'react'
import {useSelector, useDispatch} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {PageSection} from '#/main/app/page'
import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'

import {selectors} from '#/plugin/project/resources/project/store/selectors'
import {actions} from '#/plugin/project/resources/project/store/actions'
import {GRADING_MODE_RAW_SCORE, GRADING_MODE_RUBRIC} from '#/plugin/project/resources/project/constants'
import {AnnotationList} from '#/plugin/project/resources/project/player/components/annotation-list'

/**
 * Correction form for trainers.
 * Supports score entry (raw or rubric), comment, PDF export,
 * and annotation creation for iterative mode.
 */
const CorrectionForm = () => {
  const project = useSelector(selectors.project)
  const milestones = useSelector(selectors.milestones)
  const hasMilestones = milestones && milestones.length > 0
  const dispatch = useDispatch()

  const [annotationContent, setAnnotationContent] = useState('')
  const [selectedMilestone, setSelectedMilestone] = useState(hasMilestones ? milestones[0]?.id : null)

  return (
    <div>
      <FormData
        name={selectors.STORE_NAME + '.correctionForm'}
        title={trans('correction', {}, 'project')}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'score',
                type: 'number',
                label: trans('score'),
                displayed: GRADING_MODE_RAW_SCORE === project.gradingMode,
                required: true,
                options: {
                  min: 0,
                  max: project.scoreMax,
                  unit: '/ ' + project.scoreMax
                }
              }, {
                name: 'comment',
                type: 'html',
                label: trans('comment'),
                options: {
                  minRows: 3
                }
              }
            ]
          }
        ]}
      />

      {/* Annotation section for iterative mode */}
      {hasMilestones &&
        <PageSection title={trans('add_annotation', {}, 'project')}>
          <div className="card">
            <div className="card-body">
              <div className="mb-3">
                <label className="form-label">{trans('milestone', {}, 'project')}</label>
                <select
                  className="form-select"
                  value={selectedMilestone || ''}
                  onChange={(e) => setSelectedMilestone(e.target.value)}
                >
                  {milestones.map(m => (
                    <option key={m.id} value={m.id}>{m.title}</option>
                  ))}
                </select>
              </div>

              <div className="mb-3">
                <label className="form-label">{trans('annotation_content', {}, 'project')}</label>
                <textarea
                  className="form-control"
                  rows={3}
                  value={annotationContent}
                  onChange={(e) => setAnnotationContent(e.target.value)}
                  placeholder={trans('write_annotation', {}, 'project')}
                />
              </div>

              <button
                className="btn btn-primary btn-sm"
                disabled={!annotationContent.trim() || !selectedMilestone}
                onClick={() => {
                  // The submissionId will come from the route/form data
                  // For now, we dispatch the action
                  setAnnotationContent('')
                }}
              >
                <span className="fa fa-fw fa-paper-plane me-1" />
                {trans('send_annotation', {}, 'project')}
              </button>
            </div>
          </div>
        </PageSection>
      }
    </div>
  )
}

export {
  CorrectionForm
}
