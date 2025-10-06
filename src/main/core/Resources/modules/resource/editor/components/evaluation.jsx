import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {useDispatch, useSelector} from 'react-redux'

import {actions, selectors} from '#/main/core/resource/editor/store'

const enableScore = (formData) => get(formData, 'resourceNode.evaluation._enableScore') || get(formData, 'resourceNode.evaluation.scoreTotal')
const enableSuccessCondition = (formData) => get(formData, 'resourceNode.evaluation._enableSuccess')
  || get(formData, 'resourceNode.evaluation.successCondition.score')

const enableSuccessScore = (formData) => get(formData, 'resourceNode.evaluation._enableSuccessScore') || get(formData, 'resourceNode.evaluation.successCondition.score')

const enableEndMessage = (formData) => get(formData, 'resourceNode.evaluation._enableEndMessage')
  || get(formData, 'resourceNode.evaluation.endMessage')
const enableSuccessMessage = (formData) => get(formData, 'resourceNode.evaluation._enableSuccessMessage')
  || get(formData, 'resourceNode.evaluation.successMessage')
const enableFailureMessage = (formData) => get(formData, 'resourceNode.evaluation._enableFailureMessage')
  || get(formData, 'resourceNode.evaluation.failureMessage')
const enableAttemptsReachedMessage = (formData) => get(formData, 'resourceNode.evaluation._enableAttemptsReachedMessage')
  || get(formData, 'resourceNode.evaluation.attemptsReachedMessage')

const ResourceEditorEvaluation = ({
  successConditions = true
}) => {
  const dispatch = useDispatch()
  const updateProp = (propPath, propValue) => dispatch(actions.updateResourceNode(propValue, propPath))

  const workspace = useSelector(selectors.workspace)

  return (
    <EditorPage
      title={trans('parameters')}
      help={trans('Activez le suivi pédagogique pour enregistrer et suivre la progression des utilisateurs.')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'resourceNode.evaluation._enable',
              type: 'boolean',
              label: trans('Activer le suivi pédagogique', {}, 'evaluation'),
              help: trans('', {}, 'evaluation')
            }
          ]
        }, {
          title: trans('Score'),
          description: trans('Donnez un score à vos utilisateurs une fois qu\'ils ont terminé l\'activité.'),
          primary: true,
          fields: [
            {
              name: 'resourceNode.evaluation._enableScore',
              type: 'boolean',
              label: trans('Activer le score'),
              calculated: enableScore,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.scoreTotal', null)
                }
              }
            }, {
              name: 'resourceNode.evaluation.scoreTotal',
              label: trans('score_total'),
              type: 'number',
              displayed: enableScore
            }
          ]
        }, {
          title: trans('Conditions de réussite'),
          description: trans('Donnez un statut de Réussite ou d\'Échec à vos utilisateurs en fonction des conditions définies. Si aucune condition n\'est définie les utilisateurs obtiennent un statut Terminé une fois qu\'ils ont terminé l\'activité.'),
          primary: true,
          displayed: successConditions,
          fields: [
            {
              name: 'resourceNode.evaluation._enableSuccess',
              type: 'boolean',
              label: trans('enable_success_condition', {}, 'workspace'),
              //help: trans('enable_success_condition_help', {}, 'workspace'),
              calculated: enableSuccessCondition,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successCondition', null)
                  updateProp('evaluation._enableSuccessScore', false)
                }
              }
            }, {
              name: 'resourceNode.evaluation._enableSuccessScore',
              label: trans('Obtenir un score minimal', {}, 'workspace'),
              help: trans('Les utilisateurs doivent obtenir un score supérieur ou égale au pourcentage du score total défini pour réussir.'),
              type: 'boolean',
              displayed: enableSuccessCondition,
              calculated: enableSuccessScore,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successCondition.score', null)
                }
              },
              linked: [
                {
                  name: 'resourceNode.evaluation.successCondition.score',
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
            }
          ]
        }, {
          title: trans('Messages'),
          description: trans('Personnalisez les messages affichés automatiquement à vos utilisateurs lors de leur progression.'),
          primary: true,
          fields: [
            {
              name: 'resourceNode.evaluation._enableEndMessage',
              type: 'boolean',
              label: trans('Personnaliser le message de fin'),
              help: trans('Le message de fin est affiché à partir du moment où les utilisateurs ont atteint le statut "Terminé" à leur activité.'),
              calculated: enableEndMessage,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.endMessage', null)
                }
              },
              linked: [
                {
                  name: 'resourceNode.evaluation.endMessage',
                  label: trans('end_message'),
                  type: 'html',
                  required: true,
                  displayed: enableEndMessage,
                  options: {
                    workspace: workspace
                  }
                }
              ]
            }, {
              name: 'resourceNode.evaluation._enableSuccessMessage',
              type: 'boolean',
              label: trans('Personnaliser le message de réussite'),
              help: trans('Le message de réussite est affiché à partir du moment où les utilisateurs ont atteint le statut "Succès" à leur activité.'),
              calculated: enableSuccessMessage,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.successMessage', null)
                }
              },
              linked: [
                {
                  name: 'resourceNode.evaluation.successMessage',
                  label: trans('success_message'),
                  type: 'html',
                  required: true,
                  displayed: enableSuccessMessage,
                  options: {
                    workspace: workspace
                  }
                }
              ]
            }, {
              name: 'resourceNode.evaluation._enableFailureMessage',
              type: 'boolean',
              label: trans('Personnaliser le message d\'échec'),
              help: trans('Le message d\'échec est affiché à partir du moment où les utilisateurs ont atteint le statut "Echec" à leur activité.'),
              calculated: enableFailureMessage,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.failureMessage', null)
                }
              },
              linked: [
                {
                  name: 'resourceNode.evaluation.failureMessage',
                  label: trans('failure_message'),
                  type: 'html',
                  required: true,
                  displayed: enableFailureMessage,
                  options: {
                    workspace: workspace
                  }
                }
              ]
            }, {
              name: 'resourceNode.evaluation._enableAttemptsReachedMessage',
              type: 'boolean',
              label: trans('Personnaliser le message de tentatives épuisées'),
              help: trans('Le message de tentatives épuisées est affiché à partir du moment où les utilisateurs ont consommé toutes leurs tentatives disponibles.'),
              calculated: enableAttemptsReachedMessage,
              onChange: (enabled) => {
                if (!enabled) {
                  updateProp('evaluation.attemptsReachedMessage', null)
                }
              },
              linked: [
                {
                  name: 'resourceNode.evaluation.attemptsReachedMessage',
                  label: trans('Message de tentatives épuisées'),
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

ResourceEditorEvaluation.propTypes = {
  successConditions: T.bool
}

export {
  ResourceEditorEvaluation
}
