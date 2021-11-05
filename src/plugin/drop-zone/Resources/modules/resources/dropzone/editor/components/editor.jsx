import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

const Editor = props =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={`${selectors.STORE_NAME}.dropzoneForm`}
    buttons={true}
    target={['claro_dropzone_update', {id: props.dropzone.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        title: trans('general', {}, 'platform'),
        primary: true,
        fields: [
          {
            name: 'parameters.reviewType',
            type: 'choice',
            label: trans('review_type', {}, 'dropzone'),
            help: props.dropzone.parameters && constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType ?
              trans('peer_review_help', {}, 'dropzone') :
              trans('manager_review_help', {}, 'dropzone'),
            required: true,
            onChange: (value) => {
              if (constants.REVIEW_TYPE_PEER === value) {
                // force criteria
                props.updateProp('parameters.criteriaEnabled', true)
              }
            },
            options: {
              noEmpty: true,
              condensed: true,
              choices: constants.REVIEW_TYPES
            }
          }, {
            name: 'instruction',
            type: 'html',
            label: trans('instructions', {}, 'dropzone'),
            required: true,
            options: {
              workspace: props.workspace,
              minRows: 3
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-calendar',
        title: trans('planning', {}, 'dropzone'),
        fields: [
          {
            name: 'planning.type',
            type: 'choice',
            label: trans('type'),
            help: props.dropzone.planning && constants.PLANNING_TYPE_MANUAL === props.dropzone.planning.type ?
              trans('planning_manual_help', {}, 'dropzone') :
              trans('planning_auto_help', {}, 'dropzone'),
            required: true,
            options: {
              choices: constants.PLANNING_TYPES,
              condensed: true,
              noEmpty: true
            },
            linked: [
              {
                name: 'planning.state',
                type: 'choice',
                label: trans('choose_current_state', {}, 'dropzone'),
                displayed: props.dropzone.planning && constants.PLANNING_TYPE_MANUAL === props.dropzone.planning.type,
                required: true,
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: props.dropzone.parameters && props.dropzone.parameters.reviewType ?
                    constants.PLANNING_STATES[props.dropzone.parameters.reviewType] :
                    {}
                }
              }, {
                name: 'parameters.autoCloseDropsAtDropEndDate',
                type: 'boolean',
                label: trans('auto_close_drops_at_drop_end_date', {}, 'dropzone'),
                displayed: props.dropzone.planning && constants.PLANNING_TYPE_MANUAL !== props.dropzone.planning.type
              }, {
                name: 'planning.drop',
                type: 'date-range',
                label: trans('drop_range', {}, 'dropzone'),
                displayed: props.dropzone.planning && constants.PLANNING_TYPE_MANUAL !== props.dropzone.planning.type,
                required: true,
                options: {
                  time: true
                }
              }, {
                name: 'planning.review',
                type: 'date-range',
                label: trans('review_range', {}, 'dropzone'),
                displayed: props.dropzone.planning &&
                  constants.PLANNING_TYPE_MANUAL !== props.dropzone.planning.type &&
                  props.dropzone.parameters &&
                  constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType,
                required: true,
                options: {
                  time: true
                }
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-upload',
        title: trans('drop_configuration', {}, 'dropzone'),
        fields: [
          {
            name: 'parameters.dropType',
            type: 'choice',
            label: trans('drop_type', {}, 'dropzone'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: constants.DROP_TYPES
            }
          }, {
            name: 'parameters.documents',
            label: trans('allowed_document_types', {}, 'dropzone'),
            help: trans('allowed_document_types_info', {}, 'dropzone'),
            type: 'choice',
            required: true,
            options: {
              choices: constants.DOCUMENT_TYPES,
              multiple: true,
              condensed: false,
              inline: false
            }
          }, {
            name: 'parameters.revisionEnabled',
            type: 'boolean',
            label: trans('allow_revision_request', {}, 'dropzone')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-check-square-o',
        title: trans('correction'),
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
                displayed: props.dropzone.display && props.dropzone.display.displayCorrectionsToLearners
              }
            ]
          }, {
            name: 'display.showFeedback',
            type: 'boolean',
            label: trans('display_notation_message_to_learners', {}, 'dropzone'),
            linked: [
              {
                name: 'display.successMessage',
                type: 'html',
                label: trans('success_message'),
                displayed: props.dropzone.display && props.dropzone.display.showFeedback,
                required: true,
                options: {
                  workspace: props.workspace
                }
              }, {
                name: 'display.failMessage',
                type: 'html',
                label: trans('failure_message'),
                displayed: props.dropzone.display && props.dropzone.display.showFeedback,
                required: true,
                options: {
                  workspace: props.workspace
                }
              }
            ]
          }, {
            name: 'parameters.expectedCorrectionTotal',
            type: 'number',
            label: trans('expected_correction_total_label', {}, 'dropzone'),
            required: props.dropzone.parameters && constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType,
            displayed: props.dropzone.parameters && constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType,
            options: {
              min: 1
            }
          }, {
            name: 'parameters.correctionDenialEnabled',
            type: 'boolean',
            label: trans('correction_denial_label', {}, 'dropzone'),
            displayed: props.dropzone.parameters &&
              constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType &&
              props.dropzone.display &&
              props.dropzone.display.displayCorrectionsToLearners
          }, {
            name: 'display.correctionInstruction',
            type: 'html',
            label: trans('correction_instruction', {}, 'dropzone'),
            options: {
              workspace: props.workspace
            }
          }, {
            name: 'parameters.commentInCorrectionEnabled',
            type: 'boolean',
            label: trans('enable_comment', {}, 'dropzone'),
            linked: [
              {
                name: 'parameters.commentInCorrectionForced',
                type: 'boolean',
                label: trans('force_comment', {}, 'dropzone'),
                displayed: props.dropzone.parameters && props.dropzone.parameters.commentInCorrectionEnabled
              }
            ]
          }, {
            name: 'parameters.criteriaEnabled',
            type: 'boolean',
            label: trans('enable_evaluation_criteria', {}, 'dropzone'),
            required: props.dropzone.parameters && constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType,
            disabled: props.dropzone.parameters && constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType,
            linked: [
              {
                name: 'parameters.criteriaTotal',
                type: 'number',
                label: trans('evaluation_scale', {}, 'dropzone'),
                displayed: props.dropzone.parameters && props.dropzone.parameters.criteriaEnabled,
                required: true,
                options: {
                  min: 2
                }
              }, {
                name: 'parameters.criteria',
                type: 'criteria',
                label: trans('criteria', {}, 'dropzone'),
                displayed: props.dropzone.parameters && props.dropzone.parameters.criteriaEnabled,
                required: true
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-percent',
        title: trans('score'),
        fields: [
          {
            name: 'parameters.scoreMax',
            type: 'number',
            label: trans('score_total'),
            required: true,
            options: {
              min: 0
            }
          }, {
            name: 'parameters.scoreToPass',
            type: 'number',
            label: trans('score_to_pass'),
            required: true,
            options: {
              min: 0,
              unit: '%'
            }
          }, {
            name: 'display.showScore',
            type: 'boolean',
            label: trans('display_notation_to_learners', {}, 'dropzone')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-bell-o',
        title: trans('notifications', {}, 'platform'),
        fields: [
          {
            name: 'notifications.enabled',
            type: 'boolean',
            label: trans('notify_managers_on_drop', {}, 'dropzone')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions.lockDrops',
            type: 'boolean',
            label: trans('lock_drops', {}, 'dropzone'),
            help: trans('lock_drops_help', {}, 'dropzone')
          }
        ]
      }
    ]}
  />

Editor.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  dropzone: T.shape(
    DropzoneType.propTypes
  ),
  updateProp: T.func.isRequired
}

export {
  Editor
}
