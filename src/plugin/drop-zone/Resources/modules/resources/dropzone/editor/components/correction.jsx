import React, {useCallback} from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {selectors as resourceSelectors} from '#/main/core/resource'
import {actions as formActions} from '#/main/app/content/form'

const DropzoneEditorCorrection = () => {
  const workspace = useSelector(resourceSelectors.workspace)
  const dropzone = useSelector(editorSelectors.resource)

  const dispatch = useDispatch()
  const updateProp = useCallback((prop, value) => {
    dispatch(formActions.updateProp(resourceSelectors.EDITOR_NAME, 'resource.'+prop, value))
  }, [resourceSelectors.EDITOR_NAME])

  return (
    <EditorPage
      title={trans('correction', {}, 'dropzone')}
      dataPart="resource"
      definition={[
        {
          title: trans('general', {}, 'platform'),
          primary: true,
          fields: [
            {
              name: 'parameters.reviewType',
              type: 'choice',
              label: trans('review_type', {}, 'dropzone'),
              help: dropzone.parameters && constants.REVIEW_TYPE_PEER === dropzone.parameters.reviewType ?
                trans('peer_review_help', {}, 'dropzone') :
                trans('manager_review_help', {}, 'dropzone'),
              required: true,
              onChange: (value) => {
                if (constants.REVIEW_TYPE_PEER === value) {
                  // force criteria
                  updateProp('parameters.criteriaEnabled', true)
                }
              },
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.REVIEW_TYPES
              }
            }, {
              name: 'display.correctionInstruction',
              type: 'html',
              label: trans('correction_instruction', {}, 'dropzone'),
              options: {
                workspace: workspace
              }
            },
          ]
        }, {
          title: trans('advanced'),
          primary: true,
          hideTitle: true,
          fields: [
            {
              name: 'display.displayCorrectionsToLearners',
              type: 'boolean',
              label: trans('display_corrections_to_learners', {}, 'dropzone'),
              linked: [
                {
                  name: 'display.correctorDisplayed',
                  type: 'boolean',
                  label: trans('display_corrector', {}, 'dropzone'),
                  displayed: dropzone.display && dropzone.display.displayCorrectionsToLearners
                }
              ]
            }, {
              name: 'parameters.expectedCorrectionTotal',
              type: 'number',
              label: trans('expected_correction_total_label', {}, 'dropzone'),
              required: dropzone.parameters && constants.REVIEW_TYPE_PEER === dropzone.parameters.reviewType,
              displayed: dropzone.parameters && constants.REVIEW_TYPE_PEER === dropzone.parameters.reviewType,
              options: {
                min: 1
              }
            }, {
              name: 'parameters.correctionDenialEnabled',
              type: 'boolean',
              label: trans('correction_denial_label', {}, 'dropzone'),
              displayed: dropzone.parameters &&
                constants.REVIEW_TYPE_PEER === dropzone.parameters.reviewType &&
                dropzone.display &&
                dropzone.display.displayCorrectionsToLearners
            }, {
              name: 'parameters.commentInCorrectionEnabled',
              type: 'boolean',
              label: trans('enable_comment', {}, 'dropzone'),
              linked: [
                {
                  name: 'parameters.commentInCorrectionForced',
                  type: 'boolean',
                  label: trans('force_comment', {}, 'dropzone'),
                  displayed: dropzone.parameters && dropzone.parameters.commentInCorrectionEnabled
                }
              ]
            }
          ]
        }, {
          name: 'criteria',
          title: trans('criteria', {}, 'dropzone'),
          primary: true,
          fields: [
            {
              name: 'parameters.criteriaEnabled',
              type: 'boolean',
              label: trans('enable_evaluation_criteria', {}, 'dropzone'),
              required: dropzone.parameters && constants.REVIEW_TYPE_PEER === dropzone.parameters.reviewType,
              disabled: dropzone.parameters && constants.REVIEW_TYPE_PEER === dropzone.parameters.reviewType,
              linked: [
                {
                  name: 'parameters.criteriaTotal',
                  type: 'number',
                  label: trans('evaluation_scale', {}, 'dropzone'),
                  displayed: dropzone.parameters && dropzone.parameters.criteriaEnabled,
                  required: true,
                  options: {
                    min: 2
                  }
                }, {
                  name: 'parameters.criteria',
                  type: 'criteria',
                  label: trans('criteria', {}, 'dropzone'),
                  displayed: dropzone.parameters && dropzone.parameters.criteriaEnabled,
                  required: true
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
  DropzoneEditorCorrection
}
