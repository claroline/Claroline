import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import isNil from 'lodash/isNil'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {actions, selectors} from '#/main/evaluation/sequence/editor/store'

const enableScore = (formData) => get(formData, 'evaluation._enableScore', false)
  || !isNil(get(formData, 'evaluation.scoreTotal'))

const enableSuccessCondition = (formData) => get(formData, 'evaluation._enableSuccess', false)
  || enableSuccessScore(formData)
  || enableSuccessMinSuccess(formData)
  || enableSuccessMaxFailed(formData)

const enableSuccessScore = (formData) => get(formData, 'evaluation._enableSuccessScore', false)
  || !isNil(get(formData, 'evaluation.successCondition.score'))
const enableSuccessMinSuccess = (formData) => get(formData, 'evaluation._enableSuccessCount') || null !== get(formData, 'evaluation.successCondition.minSuccess', null)
const enableSuccessMaxFailed = (formData) => get(formData, 'evaluation._enableFailureCount') || null !== get(formData, 'evaluation.successCondition.maxFailed', null)

const enableCustomCertificate = (formData) => get(formData, 'evaluation._customCertificate', false)
  || !isNil(get(formData, 'evaluation.certificateTemplate'))

const enableMessages = (formData) => get(formData, 'evaluation._enableMessages', false)
  || enableEndMessage(formData)
  || enableSuccessMessage(formData)
  || enableFailureMessage(formData)
const enableEndMessage = (formData) => get(formData, 'evaluation._enableEndMessage', false)
  || !isNil(get(formData, 'evaluation.endMessage'))
const enableSuccessMessage = (formData) => get(formData, 'evaluation._enableSuccessMessage', false)
  || !isNil(get(formData, 'evaluation.successMessage'))
const enableFailureMessage = (formData) => get(formData, 'evaluation._enableFailureMessage', false)
  || !isNil(get(formData, 'evaluation.failureMessage'))

const SequenceEditorEvaluation = () => {
  const dispatch = useDispatch()
  const updateProp = (propPath, propValue) => dispatch(actions.update(propValue, propPath))

  const editedSequence = useSelector(selectors.data)
  const workspace = useSelector(selectors.workspace)

  return (
    <EditorPage
      title={trans('evaluation', {}, 'evaluation')}
      help={trans('evaluation_help', {}, 'evaluation')}
      definition={[
        {
          title: trans('score', {}, 'evaluation'),
          description: trans('score_help', {}, 'evaluation'),
          primary: true,
          enabled: enableScore,
          onToggle: (enabled) => {
            updateProp('evaluation._enableScore', enabled)
            if (!enabled) {
              updateProp('evaluation.scoreTotal', null)
              updateProp('evaluation.successCondition.score', null)
            }
          },
          fields: [
            {
              name: 'evaluation.scoreTotal',
              label: trans('score_total'),
              type: 'number',
              required: true
            }
          ]
        }, {
          title: trans('success_conditions', {}, 'evaluation'),
          description: trans('success_conditions_help', {}, 'evaluation'),
          primary: true,
          enabled: enableSuccessCondition,
          onToggle: (enabled) => {
            updateProp('evaluation._enableSuccess', enabled)
            if (!enabled) {
              updateProp('evaluation.successCondition', null)
              updateProp('evaluation._enableSuccessScore', false)
              updateProp('evaluation._enableSuccessCount', false)
              updateProp('evaluation._enableFailureCount', false)

              updateProp('evaluation.successMessage', null)
              updateProp('evaluation.failureMessage', null)
            }
          },
          fields: [
            {
              name: 'evaluation._enableSuccessScore',
              type: 'boolean',
              label: trans('enable_success_condition_score', {}, 'evaluation'),
              help: trans('enable_success_condition_score_help', {}, 'evaluation'),
              calculated: enableSuccessScore,
              displayed: enableScore,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successCondition.score', null)
                }
              },
              linked: [
                {
                  name: 'evaluation.successCondition.score',
                  label: trans('success_score', {}, 'evaluation'),
                  type: 'number',
                  required: true,
                  displayed: enableSuccessScore,
                  options: {
                    min: 0,
                    max: 100,
                    unit: '%'
                  }
                }
              ]
            }, {
              name: 'evaluation._enableSuccessCount',
              type: 'boolean',
              label: trans('enable_success_condition_success', {}, 'workspace'),
              calculated: enableSuccessMinSuccess,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successCondition.minSuccess', null)
                }
              },
              linked: [
                {
                  name: 'evaluation.successCondition.minSuccess',
                  label: trans('resources_count', {}, 'resource'),
                  type: 'number',
                  required: true,
                  displayed: enableSuccessMinSuccess,
                  options: {
                    min: 0
                  }
                }
              ]
            }, {
              name: 'evaluation._enableFailureCount',
              type: 'boolean',
              label: trans('enable_success_condition_failed', {}, 'workspace'),
              calculated: enableSuccessMaxFailed,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successCondition.maxFailed', null)
                }
              },
              linked: [
                {
                  name: 'evaluation.successCondition.maxFailed',
                  label: trans('resources_count', {}, 'resource'),
                  type: 'number',
                  required: true,
                  displayed: enableSuccessMaxFailed,
                  options: {
                    min: 0
                  }
                }
              ]
            }
          ]
        }, {
          title: trans('certification', {}, 'evaluation'),
          description: trans('certification_help', {}, 'evaluation'),
          primary: true,
          enabled: (formData) => get(formData, 'evaluation.certified', false),
          onToggle: (enabled) => {
            updateProp('evaluation.certified', enabled)
            if (!enabled) {
              updateProp('evaluation._customCertificate', false)
              updateProp('evaluation.certificateTemplate', null)
            }
          },
          fields: [
            {
              name: 'evaluation._customCertificate',
              label: trans('customize_certificate', {}, 'evaluation'),
              help: trans('customize_certificate_help', {}, 'evaluation'),
              type: 'boolean',
              calculated: enableCustomCertificate,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.certificateTemplate', null)
                }
              },
              linked: [
                {
                  name: 'evaluation.certificateTemplate',
                  label: trans('certificate_template', {}, 'evaluation'),
                  type: 'template',
                  displayed: enableCustomCertificate,
                  required: true,
                  options: {
                    templateType: enableSuccessCondition(editedSequence) ? 'evaluation_success_certificate' : 'evaluation_participation_certificate'
                  }
                }
              ]
            }
          ]
        }, {
          title: trans('custom_feedbacks', {}, 'evaluation'),
          description: trans('custom_feedbacks_help', {}, 'evaluation'),
          primary: true,
          enabled: enableMessages,
          onToggle: (enabled) => {
            updateProp('evaluation._enableMessages', enabled)
            if (!enabled) {
              updateProp('evaluation._enableEndMessage', false)
              updateProp('evaluation.endMessage', null)

              updateProp('evaluation._enableSuccessMessage', false)
              updateProp('evaluation.successMessage', null)

              updateProp('evaluation._enableFailureMessage', false)
              updateProp('evaluation.failureMessage', null)
            }
          },
          fields: [
            {
              name: 'evaluation._enableEndMessage',
              type: 'boolean',
              label: trans('add_end_message', {}, 'evaluation'),
              help: trans('add_end_message_help', {}, 'evaluation'),
              calculated: enableEndMessage,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.endMessage', null)
                }
              },
              linked: [
                {
                  name: 'evaluation.endMessage',
                  label: trans('end_message', {}, 'evaluation'),
                  type: 'html',
                  required: true,
                  displayed: enableEndMessage,
                  options: {
                    workspace: workspace
                  }
                }
              ]
            }, {
              name: 'evaluation._enableSuccessMessage',
              type: 'boolean',
              label: trans('add_success_message', {}, 'evaluation'),
              help: trans('add_success_message_help', {}, 'evaluation'),
              calculated: enableSuccessMessage,
              displayed: enableSuccessCondition,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successMessage', null)
                }
              },
              linked: [
                {
                  name: 'evaluation.successMessage',
                  label: trans('success_message', {}, 'evaluation'),
                  type: 'html',
                  required: true,
                  displayed: enableSuccessMessage,
                  options: {
                    workspace: workspace
                  }
                }
              ]
            }, {
              name: 'evaluation._enableFailureMessage',
              type: 'boolean',
              label: trans('add_failure_message', {}, 'evaluation'),
              help: trans('add_failure_message_help', {}, 'evaluation'),
              calculated: enableFailureMessage,
              displayed: enableSuccessCondition,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.failureMessage', null)
                }
              },
              linked: [
                {
                  name: 'evaluation.failureMessage',
                  label: trans('failure_message', {}, 'evaluation'),
                  type: 'html',
                  required: true,
                  displayed: enableFailureMessage,
                  options: {
                    workspace: workspace
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
  SequenceEditorEvaluation
}
