import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {selectors as editorSelectors} from '#/main/core/resource/editor'
import {selectors as resourceSelectors} from '#/main/core/resource'

import {
  SUBMISSION_TYPES,
  SUBMISSION_TYPE_FILE,
  GRADING_MODES,
  GRADING_MODE_RAW_SCORE,
  DEADLINE_TYPES,
  DEADLINE_TYPE_FIXED,
  DEADLINE_TYPE_RELATIVE
} from '#/plugin/project/resources/project/constants'

const ProjectEditorParameters = () => {
  const workspace = useSelector(resourceSelectors.workspace)
  const project = useSelector(editorSelectors.resource)

  return (
    <EditorPage
      title={trans('parameters')}
      dataPart="resource"
      definition={[
        {
          title: trans('instructions', {}, 'project'),
          primary: true,
          fields: [
            {
              name: 'instruction',
              type: 'html',
              label: trans('instruction', {}, 'project'),
              options: {
                workspace: workspace,
                minRows: 3
              }
            }, {
              name: 'estimatedDuration',
              type: 'number',
              label: trans('estimated_duration', {}, 'project'),
              help: trans('estimated_duration_help', {}, 'project'),
              options: {
                min: 0,
                unit: trans('minutes')
              }
            }
          ]
        }, {
          title: trans('submission', {}, 'project'),
          primary: true,
          fields: [
            {
              name: 'submissionType',
              type: 'choice',
              label: trans('submission_type', {}, 'project'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: SUBMISSION_TYPES
              },
              linked: [
                {
                  name: 'allowedFileTypes',
                  type: 'choice',
                  label: trans('allowed_file_types', {}, 'project'),
                  help: trans('allowed_file_types_help', {}, 'project'),
                  displayed: project && SUBMISSION_TYPE_FILE === project.submissionType,
                  options: {
                    choices: {
                      'application/pdf': 'PDF',
                      'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'Word (DOCX)',
                      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'Excel (XLSX)',
                      'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'PowerPoint (PPTX)',
                      'image/*': trans('images')
                    },
                    multiple: true,
                    condensed: false,
                    inline: false
                  }
                }
              ]
            }, {
              name: 'templateFile',
              type: 'file',
              label: trans('template_file', {}, 'project'),
              help: trans('template_file_help', {}, 'project')
            }
          ]
        }, {
          title: trans('grading', {}, 'project'),
          primary: true,
          fields: [
            {
              name: 'gradingMode',
              type: 'choice',
              label: trans('grading_mode', {}, 'project'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: GRADING_MODES
              },
              linked: [
                {
                  name: 'scoreMax',
                  type: 'number',
                  label: trans('score_max'),
                  displayed: project && GRADING_MODE_RAW_SCORE === project.gradingMode,
                  required: true,
                  options: {
                    min: 0
                  }
                }
              ]
            }
          ]
        }, {
          title: trans('deadline', {}, 'project'),
          primary: true,
          fields: [
            {
              name: 'deadlineType',
              type: 'choice',
              label: trans('deadline_type', {}, 'project'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: DEADLINE_TYPES
              },
              linked: [
                {
                  name: 'deadlineDate',
                  type: 'date',
                  label: trans('deadline_date', {}, 'project'),
                  displayed: project && DEADLINE_TYPE_FIXED === project.deadlineType,
                  required: true,
                  options: {
                    time: true
                  }
                }, {
                  name: 'deadlineDays',
                  type: 'number',
                  label: trans('deadline_days', {}, 'project'),
                  help: trans('deadline_days_help', {}, 'project'),
                  displayed: project && DEADLINE_TYPE_RELATIVE === project.deadlineType,
                  required: true,
                  options: {
                    min: 1,
                    unit: trans('days')
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

export {
  ProjectEditorParameters
}
