import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

const EditorComponent = props =>
  <section className="resource-section">
    <h2>{trans('configuration', {}, 'platform')}</h2>
    <FormContainer
      level={3}
      name="dropzoneForm"
      sections={[
        {
          id: 'general',
          title: trans('general', {}, 'platform'),
          primary: true,
          fields: [
            {
              name: 'parameters.reviewType',
              type: 'enum',
              label: trans('review_type', {}, 'dropzone'),
              help: trans(constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType ? 'peer_review_help':'manager_review_help', {}, 'dropzone'),
              required: true,
              onChange: (value) => {
                if (constants.REVIEW_TYPE_PEER === value) {
                  // force criteria
                  props.updateProp('parameters.criteriaEnabled', true)
                }
              },
              options: {
                noEmpty: true,
                choices: constants.REVIEW_TYPES
              }
            }, {
              name: 'instruction',
              type: 'html',
              label: trans('instructions', {}, 'dropzone'),
              required: true,
              options: {
                minRows: 3
              }
            }
          ]
        }, {
          id: 'planning',
          icon: 'fa fa-fw fa-calendar',
          title: trans('planning', {}, 'dropzone'),
          fields: [
            {
              name: 'planning.type',
              type: 'enum',
              label: trans('type'),
              help: trans(constants.PLANNING_TYPE_MANUAL === props.dropzone.planning.type ? 'planning_manual_help':'planning_auto_help', {}, 'dropzone'),
              required: true,
              options: {
                choices: constants.PLANNING_TYPES,
                noEmpty: true
              },
              linked: [
                {
                  name: 'planning.state',
                  type: 'enum',
                  label: trans('choose_current_state', {}, 'dropzone'),
                  displayed: constants.PLANNING_TYPE_MANUAL === props.dropzone.planning.type,
                  required: true,
                  options: {
                    noEmpty: true,
                    choices: constants.PLANNING_STATES[props.dropzone.parameters.reviewType]
                  }
                }, {
                  name: 'parameters.autoCloseDropsAtDropEndDate',
                  type: 'boolean',
                  label: trans('auto_close_drops_at_drop_end_date', {}, 'dropzone'),
                  displayed: constants.PLANNING_TYPE_MANUAL !== props.dropzone.planning.type
                }, {
                  name: 'planning.drop',
                  type: 'date-range',
                  label: trans('drop_range', {}, 'dropzone'),
                  displayed: constants.PLANNING_TYPE_MANUAL !== props.dropzone.planning.type,
                  required: true
                }, {
                  name: 'planning.review',
                  type: 'date-range',
                  label: trans('review_range', {}, 'dropzone'),
                  displayed: constants.PLANNING_TYPE_MANUAL !== props.dropzone.planning.type && constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType,
                  required: true
                }
              ]
            }
          ]
        }, {
          id: 'drop-configuration',
          icon: 'fa fa-fw fa-upload',
          title: trans('drop_configuration', {}, 'dropzone'),
          fields: [
            {
              name: 'parameters.dropType',
              type: 'enum',
              label: trans('drop_type', {}, 'dropzone'),
              required: true,
              options: {
                noEmpty: true,
                choices: constants.DROP_TYPES
              }
            }, {
              name: 'parameters.documents',
              label: trans('allowed_document_types', {}, 'dropzone'),
              help: trans('allowed_document_types_info', {}, 'dropzone'),
              type: 'enum',
              required: true,
              options: {
                choices: constants.DOCUMENT_TYPES,
                multiple: true,
                condensed: false
              }
            }
          ]
        }, {
          id: 'correction',
          icon: 'fa fa-fw fa-check-square-o',
          title: trans('correction', {}, 'dropzone'),
          fields: [
            {
              name: 'parameters.scoreMax',
              type: 'number',
              label: trans('score_max', {}, 'dropzone'),
              required: true,
              options: {
                min: 0
              }
            }, {
              name: 'parameters.scoreToPass',
              type: 'number',
              label: trans('score_to_pass', {}, 'dropzone'),
              required: true,
              options: {
                min: 0
              }
            }, {
              name: 'parameters.expectedCorrectionTotal',
              type: 'number',
              label: trans('expected_correction_total_label', {}, 'dropzone'),
              required: constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType,
              displayed: constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType,
              options: {
                min: 1
              }
            }, {
              name: 'parameters.correctionDenialEnabled',
              type: 'boolean',
              label: trans('correction_denial_label', {}, 'dropzone'),
              displayed: constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType && props.dropzone.display.displayCorrectionsToLearners
            }, {
              name: 'display.correctionInstruction',
              type: 'html',
              label: trans('correction_instruction', {}, 'dropzone')
            }, {
              name: 'parameters.commentInCorrectionEnabled',
              type: 'boolean',
              label: trans('enable_comment', {}, 'dropzone'),
              linked: [
                {
                  name: 'parameters.commentInCorrectionForced',
                  type: 'boolean',
                  label: trans('force_comment', {}, 'dropzone'),
                  displayed: props.dropzone.parameters.commentInCorrectionEnabled
                }
              ]
            }, {
              name: 'parameters.criteriaEnabled',
              type: 'boolean',
              label: trans('enable_evaluation_criteria', {}, 'dropzone'),
              required: constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType,
              disabled: constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType,
              linked: [
                {
                  name: 'parameters.criteriaTotal',
                  type: 'number',
                  label: trans('evaluation_scale', {}, 'dropzone'),
                  displayed: props.dropzone.parameters.criteriaEnabled,
                  required: true,
                  options: {
                    min: 2
                  }
                }, {
                  name: 'parameters.criteria',
                  type: 'criteria',
                  label: trans('criteria', {}, 'dropzone'),
                  displayed: props.dropzone.parameters.criteriaEnabled,
                  required: true
                }
              ]
            }
          ]
        }, {
          id: 'display',
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'display.showScore',
              type: 'boolean',
              label: trans('display_notation_to_learners', {}, 'dropzone')
            }, {
              name: 'display.showFeedback',
              type: 'boolean',
              label: trans('display_notation_message_to_learners', {}, 'dropzone'),
              linked: [
                {
                  name: 'display.successMessage',
                  type: 'html',
                  label: trans('success_message', {}, 'dropzone'),
                  displayed: props.dropzone.display.showFeedback,
                  required: true
                }, {
                  name: 'display.failMessage',
                  type: 'html',
                  label: trans('fail_message', {}, 'dropzone'),
                  displayed: props.dropzone.display.showFeedback,
                  required: true
                }
              ]
            }, {
              name: 'display.displayCorrectionsToLearners',
              type: 'boolean',
              label: trans('display_corrections_to_learners', {}, 'dropzone')
            }
          ]
        }, {
          id: 'notification',
          icon: 'fa fa-fw fa-bell-o',
          title: trans('notifications', {}, 'platform'),
          fields: [
            {
              name: 'notifications.enabled',
              type: 'boolean',
              label: trans('notify_managers_on_drop', {}, 'dropzone')
            }
          ]
        }
      ]}
    />
  </section>

EditorComponent.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes),
  updateProp: T.func.isRequired
}

const Editor = connect(
  state => ({
    dropzone: formSelect.data(formSelect.form(state, 'dropzoneForm'))
  }),
  dispatch => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp('dropzoneForm', propName, propValue))
    }
  })
)(EditorComponent)

export {
  Editor
}
