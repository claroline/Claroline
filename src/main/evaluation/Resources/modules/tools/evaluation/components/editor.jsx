import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {Alert} from '#/main/app/components/alert'
import {FormContent} from '#/main/app/content/form/containers/content'
import {constants as listConst} from '#/main/app/content/list/constants'
import {ToolEditor, selectors as toolSelectors} from '#/main/core/tool'
import {MODAL_RESOURCES} from '#/main/core/modals/resources'
import {ResourceList} from '#/main/core/resource/components/list'

import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {actions as formActions} from '#/main/app/content/form/store'

// easy selection for evaluation
const enableSuccessCondition = (workspace) => get(workspace, 'evaluation._enableSuccess')
  || get(workspace, 'evaluation.successCondition.score')
  || undefined !== get(workspace, 'evaluation.successCondition.minSuccess')
  || undefined !== get(workspace, 'evaluation.successCondition.maxFailed')
const enableSuccessScore = (workspace) => get(workspace, 'evaluation._enableSuccessScore') || get(workspace, 'evaluation.successCondition.score')
const enableSuccessMinSuccess = (workspace) => get(workspace, 'evaluation._enableSuccessCount') || null !== get(workspace, 'evaluation.successCondition.minSuccess', null)
const enableSuccessMaxFailed = (workspace) => get(workspace, 'evaluation._enableFailureCount') || null !== get(workspace, 'evaluation.successCondition.maxFailed', null)


const EvaluationEditor = (props) => {
  const dispatch = useDispatch()

  const contextType = useSelector(toolSelectors.contextType)
  const contextId = useSelector(toolSelectors.contextId)
  const contextData = useSelector(toolSelectors.contextData)

  useEffect(() => {
    if (contextId) {
      dispatch(formActions.load(toolSelectors.EDITOR_NAME, {evaluation: get(contextData, 'evaluation')}))
    }
  }, [contextType, contextId])

  return (
    <ToolEditor>
      {contextId &&
        <>
          <FormContent
            name={toolSelectors.EDITOR_NAME}
            definition={[
              {
                icon: 'fa fa-fw fa-award',
                title: trans('evaluation'),
                fields: [
                  {
                    name: 'evaluation.estimatedDuration',
                    label: trans('estimated_duration'),
                    type: 'number',
                    options: {
                      unit: trans('minutes')
                    }
                  }, {
                    name: 'evaluation._enable',
                    type: 'boolean',
                    label: trans('Activer le suivi pédagogique', {}, 'evaluation'),
                    help: trans('', {}, 'evaluation')
                  }, {
                    name: 'evaluation.scoreTotal',
                    label: trans('score_total'),
                    type: 'number'
                  }, {
                    name: 'evaluation._enableSuccess',
                    type: 'boolean',
                    label: trans('enable_success_condition', {}, 'workspace'),
                    help: trans('enable_success_condition_help', {}, 'workspace'),
                    calculated: enableSuccessCondition,
                    onChange: (enabled) => {
                      if (!enabled) {
                        props.updateProp('evaluation.successCondition', null)
                        props.updateProp('evaluation._enableSuccessScore', false)
                        props.updateProp('evaluation._enableSuccessCount', false)
                        props.updateProp('evaluation._enableFailureCount', false)
                      }
                    },
                    linked: [
                      {
                        name: 'evaluation._enableSuccessScore',
                        label: trans('Obtenir un score minimal', {}, 'workspace'),
                        type: 'boolean',
                        displayed: enableSuccessCondition,
                        calculated: enableSuccessScore,
                        onChange: (enabled) => {
                          if (!enabled) {
                            props.updateProp('evaluation.successCondition.score', null)
                          }
                        },
                        linked: [
                          {
                            name: 'evaluation.successCondition.score',
                            label: trans('score_to_pass'),
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
                        displayed: enableSuccessCondition,
                        calculated: enableSuccessMinSuccess,
                        onChange: (enabled) => {
                          if (!enabled) {
                            props.updateProp('evaluation.successCondition.minSuccess', null)
                          }
                        },
                        linked: [
                          {
                            name: 'evaluation.successCondition.minSuccess',
                            label: trans('count_resources', {}, 'resource'),
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
                        displayed: enableSuccessCondition,
                        calculated: enableSuccessMaxFailed,
                        onChange: (enabled) => {
                          if (!enabled) {
                            props.updateProp('evaluation.successCondition.maxFailed', null)
                          }
                        },
                        linked: [
                          {
                            name: 'evaluation.successCondition.maxFailed',
                            label: trans('count_resources', {}, 'resource'),
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
                  }
                ]
              }
            ]}
          />

          <hr />

          <Alert
            className="mt-3"
            type="info"
            title={trans('workspace_requirements_help_title', {}, 'evaluation')}
          >
            {trans('workspace_requirements_help_description', {}, 'evaluation')}
          </Alert>

          <ResourceList
            className="mb-3"
            name={selectors.STORE_NAME+'.requiredResources'}
            url={['apiv2_workspace_required_resource_list', {workspace: props.contextId}]}
            delete={{
              url: ['apiv2_workspace_required_resource_remove', {workspace: props.contextId}]
            }}
            actions={undefined}
            display={{
              current: listConst.DISPLAY_LIST_SM
            }}
          />

          <Button
            className="btn btn-primary w-100 mb-3"
            type={MODAL_BUTTON}
            primary={true}
            size="lg"
            label={trans('add_resources')}
            modal={[MODAL_RESOURCES, {
              root: props.workspaceRoot,
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                callback: () => props.addRequiredResources(props.contextId, selected)
              })
            }]}
          />
        </>
      }
    </ToolEditor>
  )
}

EvaluationEditor.propTypes = {
  contextId: T.string.isRequired,
  addRequiredResources: T.func.isRequired,
  workspaceRoot: T.object.isRequired
}

export {
  EvaluationEditor
}
