import React, {useCallback} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isNil from 'lodash/isNil'

import {trans} from '#/main/app/intl'
import {FormContent, actions as formActions, selectors as formSelectors} from '#/main/app/content/form'
import isEmpty from 'lodash/isEmpty'

const enableScore = (formData) => get(formData, 'evaluation.scored', false)

const enableSuccessCondition = (formData) => {
  if (get(formData, 'evaluation._enableSuccess', false)) {
    return true
  }

  // success conditions are enabled if at least one condition has a value
  if (get(formData, 'evaluation.successCondition')) {
    return -1 !== Object.keys(get(formData, 'evaluation.successCondition')).findIndex((conditionName) => isSuccessConditionEnabled(conditionName, formData))
  }

  return false
}

const isSuccessConditionEnabled = (conditionName, formData) => {
  return get(formData, `evaluation.${conditionName}Enabled`)
    || !isNil(get(formData, `evaluation.successCondition.${conditionName}`))
}

const enableCustomCertificate = (formData) => get(formData, 'evaluation._customCertificate', false)
  || !isNil(get(formData, 'evaluation.certificateTemplate'))

const enableMessages = (formData) => get(formData, 'evaluation._enableMessages', false)
  || enableEndMessage(formData)
  || enableSuccessMessage(formData)
  || enableFailureMessage(formData)
  || enableAttemptsReachedMessage(formData)

const enableEndMessage = (formData) => get(formData, 'evaluation._enableEndMessage', false)
  || !isNil(get(formData, 'evaluation.endMessage'))
const enableSuccessMessage = (formData) => get(formData, 'evaluation._enableSuccessMessage', false)
  || !isNil(get(formData, 'evaluation.successMessage'))
const enableFailureMessage = (formData) => get(formData, 'evaluation._enableFailureMessage', false)
  || !isNil(get(formData, 'evaluation.failureMessage'))

const enableAttemptsReachedMessage = (formData) => get(formData, 'evaluation._enableAttemptsReachedMessage')
  || get(formData, 'evaluation.attemptsReachedMessage')

const EvaluationForm = ({
  name,
  workspace,
  score = false,
  successConditions = [],
  attempts = false,
  certification = false,
  disabled = false
}) => {
  const dispatch = useDispatch()
  const updateProp = useCallback((propPath, propValue) => {
    return dispatch(formActions.updateProp(name, propPath, propValue))
  }, [name])

  const formData = useSelector((state) => formSelectors.data(formSelectors.form(state, name)))

  return (
    <FormContent
      name={name}
      level={3}
      displayLevel={5}
      disabled={disabled}
      definition={[
        {
          icon: 'fa fa-fw fa-arrow-rotate-right',
          title: trans('attempts_count', {}, 'evaluation'),
          description: trans('attempts_count_help', {}, 'evaluation'),
          primary: true,
          hideTitle: false,
          displayed: attempts,
          fields: [
            {
              name: 'evaluation.maxAttempts',
              label: trans('max_attempts', {}, 'evaluation'),
              type: 'number',
              placeholder: trans('unlimited_attempts', {}, 'evaluation'),
              options: {
                min: 0
              },
              onChange: (value) => {
                if (isEmpty(value)) {
                  updateProp('evaluation._enableAttemptsReachedMessage', false)
                  updateProp('evaluation.attemptsReachedMessage', null)
                }
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-percent',
          title: trans('score', {}, 'evaluation'),
          description: trans('score_help', {}, 'evaluation'),
          primary: true,
          displayed: score,
          enabled: enableScore,
          onToggle: (enabled) => {
            updateProp('evaluation.scored', enabled)
            if (!enabled) {
              updateProp('evaluation.scoreTotal', null)
              if (-1 !== successConditions.findIndex(condition => 'score' === condition.name)) {
                updateProp('evaluation.successCondition.score', null)
              }

            } else {
              updateProp('evaluation.scoreTotal', 100)
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
          icon: 'fa fa-fw fa-check-double',
          title: trans('success_conditions', {}, 'evaluation'),
          description: trans('success_conditions_help', {}, 'evaluation'),
          primary: true,
          enabled: enableSuccessCondition,
          onToggle: (enabled) => {
            updateProp('evaluation._enableSuccess', enabled)
            if (!enabled) {
              // removes all success conditions data
              updateProp('evaluation.successCondition', null)

              successConditions.map(successCondition => {
                updateProp(`evaluation.${successCondition.name}Enabled`, false)
              })

              // removes messages linked to success conditions
              updateProp('evaluation._enableSuccessMessage', false)
              updateProp('evaluation.successMessage', null)
              updateProp('evaluation._enableFailureMessage', false)
              updateProp('evaluation.failureMessage', null)
            } else {
              updateProp('evaluation._enableEndMessage', false)
              updateProp('evaluation.endMessage', null)
            }
          },
          fields: successConditions.map(successCondition => ({
            name: `evaluation.${successCondition.name}Enabled`,
            type: 'boolean',
            label: successCondition.label,
            help: successCondition.help,
            displayed: (formData) => {
              if (undefined === successCondition.displayed) {
                return true
              }

              if (typeof successCondition.displayed === 'function') {
                return successCondition.displayed(get(formData, 'evaluation'))
              }

              return successCondition.displayed
            },
            calculated: (formData) => isSuccessConditionEnabled(successCondition.name, formData),
            onChange: (enabled) => {
              if (!enabled) {
                updateProp(`evaluation.successCondition.${successCondition.name}`, null)
              }
            },
            linked: successCondition.fields.map(field => ({
              ...field,
              name: `evaluation.successCondition.${field.name}`,
              displayed: (formData) => isSuccessConditionEnabled(successCondition.name, formData)
            }))
          }))
        }, {
          icon: 'fa fa-fw fa-certificate',
          title: trans('certification', {}, 'evaluation'),
          description: trans('certification_help', {}, 'evaluation'),
          primary: true,
          displayed: certification,
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
                    templateType: enableSuccessCondition(formData) ? 'evaluation_success_certificate' : 'evaluation_participation_certificate'
                  }
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-comment',
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

              if (attempts) {
                updateProp('evaluation._enableAttemptsReachedMessage', false)
                updateProp('evaluation.attemptsReachedMessage', null)
              }
            }
          },
          fields: [
            {
              name: 'evaluation._enableEndMessage',
              type: 'boolean',
              label: trans('add_end_message', {}, 'evaluation'),
              help: trans('add_end_message_help', {}, 'evaluation'),
              calculated: enableEndMessage,
              displayed: (formData) => !enableSuccessCondition(formData),
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
            }, {
              name: 'evaluation._enableAttemptsReachedMessage',
              type: 'boolean',
              label: trans('add_attempts_reached_message', {}, 'evaluation'),
              help: trans('add_attempts_reached_message_help', {}, 'evaluation'),
              calculated: enableAttemptsReachedMessage,
              displayed: (evaluationData) => attempts && evaluationData && !isNil(get(formData, 'evaluation.maxAttempts')),
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.attemptsReachedMessage', null)
                }
              },
              linked: [
                {
                  name: 'evaluation.attemptsReachedMessage',
                  label: trans('attempts_reached_message', {}, 'evaluation'),
                  type: 'html',
                  required: true,
                  displayed: enableAttemptsReachedMessage,
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

EvaluationForm.propTypes = {
  /**
   * The name of the parent form.
   */
  name: T.string.isRequired,
  disabled: T.bool,
  /**
   * The parent workspace of the evaluated content.
   */
  workspace: T.object,
  /**
   * Enable score parameters.
   */
  score: T.bool,
  /**
   * Enable certification parameters.
   */
  certification: T.bool,
  /**
   * Enable attempts parameters.
   */
  attempts: T.bool,
  /**
   * The list of implemented success conditions.
   */
  successConditions: T.arrayOf(T.shape({
    /**
     * The name of the condition.
     */
    name: T.string.isRequired,
    /**
     * The label for the toggle button.
     */
    label: T.string.isRequired,
    /**
     * Additional help messages to describe the condition.
     */
    help: T.oneOfType([T.string, T.arrayOf(T.string)]),
    /**
     * The list of form fields to configure the success condition
     */
    fields: T.arrayOf(T.object)
  }))
}

export {
  EvaluationForm
}
