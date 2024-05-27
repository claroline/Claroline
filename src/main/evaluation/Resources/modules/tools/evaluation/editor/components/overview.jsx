import React, {useCallback} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {selectors as toolSelectors} from '#/main/core/tool'

import {actions as formActions} from '#/main/app/content/form/store'
import {ToolEditorOverview} from '#/main/core/tool/editor'

// easy selection for evaluation
const enableScore = (workspace) => get(workspace, 'evaluation._enableScore') || get(workspace, 'evaluation.scoreTotal')
const enableSuccessCondition = (workspace) => get(workspace, 'evaluation._enableSuccess')
  || get(workspace, 'evaluation.successCondition.score')
  || undefined !== get(workspace, 'evaluation.successCondition.minSuccess')
  || undefined !== get(workspace, 'evaluation.successCondition.maxFailed')
const enableSuccessScore = (workspace) => get(workspace, 'evaluation._enableSuccessScore') || get(workspace, 'evaluation.successCondition.score')
const enableSuccessMinSuccess = (workspace) => get(workspace, 'evaluation._enableSuccessCount') || null !== get(workspace, 'evaluation.successCondition.minSuccess', null)
const enableSuccessMaxFailed = (workspace) => get(workspace, 'evaluation._enableFailureCount') || null !== get(workspace, 'evaluation.successCondition.maxFailed', null)

const EvaluationEditorOverview = () => {
  const dispatch = useDispatch()
  const updateProp = useCallback((prop, value) => {
    dispatch(formActions.updateProp(toolSelectors.EDITOR_NAME, prop, value))
  }, [toolSelectors.EDITOR_NAME])

  const contextType = useSelector(toolSelectors.contextType)

  return (
    <ToolEditorOverview
      definition={[
        {
          title: trans('general'),
          displayed: 'workspace' === contextType,
          hideTitle: true,
          primary: true,
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
            }
          ]
        }, {
          title: trans('score'),
          subtitle: trans('Donnez un score à vos utilisateurs une fois qu\'ils ont terminé toutes les l\'activités de l\'espace.'),
          displayed: 'workspace' === contextType,
          primary: true,
          fields: [
            {
              name: 'evaluation._enableScore',
              type: 'boolean',
              label: trans('Activer le score'),
              calculated: enableScore,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.scoreTotal', null)
                }
              }
            }, {
              name: 'evaluation.scoreTotal',
              label: trans('score_total'),
              type: 'number',
              disabled: (data) => !enableScore(data)
            }
          ]
        }, {
          title: trans('Conditions de réussite'),
          subtitle: trans('Donnez un statut de Réussite ou d\'Échec à vos utilisateurs en fonction des conditions définies. Si aucune condition n\'est définie les utilisateurs obtiennent un statut Terminé une fois qu\'ils ont terminé toutes les activités de l\'espace.'),
          displayed: 'workspace' === contextType,
          primary: true,
          fields: [
            {
              name: 'evaluation._enableSuccess',
              type: 'boolean',
              label: trans('enable_success_condition', {}, 'workspace'),
              //help: trans('enable_success_condition_help', {}, 'workspace'),
              calculated: enableSuccessCondition,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successCondition', null)
                  updateProp('evaluation._enableSuccessScore', false)
                  updateProp('evaluation._enableSuccessCount', false)
                  updateProp('evaluation._enableFailureCount', false)
                }
              }
            }, {
              name: 'evaluation._enableSuccessScore',
              label: trans('Obtenir un score minimal', {}, 'workspace'),
              help: trans('Les utilisateurs doivent obtenir un score supérieur ou égale au pourcentage du score total défini pour réussir.'),
              type: 'boolean',
              disabled: (data) => !enableSuccessCondition(data),
              calculated: enableSuccessScore,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successCondition.score', null)
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
              disabled: (data) => !enableSuccessCondition(data),
              calculated: enableSuccessMinSuccess,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successCondition.minSuccess', null)
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
              disabled: (data) => !enableSuccessCondition(data),
              calculated: enableSuccessMaxFailed,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successCondition.maxFailed', null)
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
      ]}
    />
  )
}

export {
  EvaluationEditorOverview
}
