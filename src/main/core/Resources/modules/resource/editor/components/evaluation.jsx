import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const enableScore = (formData) => get(formData, 'resourceNode.evaluation._enableScore') || get(formData, 'resourceNode.evaluation.scoreTotal')
const enableSuccessCondition = (formData) => get(formData, 'resourceNode.evaluation._enableSuccess')
  || get(formData, 'resourceNode.evaluation.successCondition.score')
const enableMessages = (formData) => get(formData, 'resourceNode.evaluation._enableMessages')
  || get(formData, 'resourceNode.evaluation.endMessage')
  || get(formData, 'resourceNode.evaluation.successMessage')
  || get(formData, 'resourceNode.evaluation.failureMessage')

const enableSuccessScore = (formData) => get(formData, 'resourceNode.evaluation._enableSuccessScore') || get(formData, 'resourceNode.evaluation.successCondition.score')

const ResourceEditorEvaluation = (props) =>
  <EditorPage
    title={trans('evaluation')}
    help={trans('Activez le suivi pédagogique pour enregistrer et suivre la progression des utilisateurs.')}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'resourceNode.evaluation.estimatedDuration',
            label: trans('estimated_duration'),
            type: 'number',
            options: {
              unit: trans('minutes')
            }
          }, {
            name: 'resourceNode.evaluation._enable',
            type: 'boolean',
            label: trans('Activer le suivi pédagogique', {}, 'evaluation'),
            help: trans('', {}, 'evaluation')
          }, {
            name: 'resourceNode.evaluation.required',
            label: trans('require_resource', {}, 'resource'),
            type: 'boolean',
            help: trans('require_resource_help', {}, 'resource'),
            onChange: (required) => {
              if (!required) {
                updateProp('resourceNode.evaluation.evaluated', false)
              }
            },
            linked: [
              {
                name: 'resourceNode.evaluation.evaluated',
                label: trans('evaluate_resource', {}, 'resource'),
                type: 'boolean',
                help: trans('evaluate_resource_help', {}, 'resource'),
                displayed: (resource) => get(resource, 'resourceNode.evaluation.required', false)
              }
            ]
          }
        ]
      }, {
        title: trans('Score'),
        subtitle: trans('Donnez un score à vos utilisateurs une fois qu\'ils ont terminé l\'activité.'),
        primary: true,
        fields: [
          {
            name: 'resourceNode.evaluation._enableScore',
            type: 'boolean',
            label: trans('Activer le score'),
            calculated: enableScore,
            onChange: (enabled) => {
              if (!enabled) {
                updateProp('resourceNode.evaluation.scoreTotal', null)
              }
            }
          }, {
            name: 'resourceNode.evaluation.scoreTotal',
            label: trans('score_total'),
            type: 'number',
            disabled: (data) => !enableScore(data)
          },
        ]
      }, {
        title: trans('Conditions de réussite'),
        subtitle: trans('Donnez un statut de Réussite ou d\'Échec à vos utilisateurs en fonction des conditions définies. Si aucune condition n\'est définie les utilisateurs obtiennent un statut Terminé une fois qu\'ils ont terminé l\'activités.'),
        primary: true,
        fields: [
          {
            name: 'resourceNode.evaluation._enableSuccess',
            type: 'boolean',
            label: trans('enable_success_condition', {}, 'workspace'),
            //help: trans('enable_success_condition_help', {}, 'workspace'),
            calculated: enableSuccessCondition,
            onChange: (enabled) => {
              if (!enabled) {
                updateProp('resourceNode.evaluation.successCondition', null)
                updateProp('resourceNode.evaluation._enableSuccessScore', false)
              }
            }
          }, {
            name: 'resourceNode.evaluation._enableSuccessScore',
            label: trans('Obtenir un score minimal', {}, 'workspace'),
            help: trans('Les utilisateurs doivent obtenir un score supérieur ou égale au pourcentage du score total défini pour réussir.'),
            type: 'boolean',
            disabled: (data) => !enableSuccessCondition(data),
            calculated: enableSuccessScore,
            onChange: (enabled) => {
              if (!enabled) {
                updateProp('resourceNode.evaluation.successCondition.score', null)
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
        subtitle: trans('Personnalisez les messages affichés automatiquement à vos utilisateurs lors de leur progression.'),
        primary: true,
        fields: [
          {
            name: 'resourceNode.evaluation._enableMessages',
            type: 'boolean',
            label: trans('Activer les messages personnalisés'),
            calculated: enableMessages,
            onChange: (enabled) => {
              if (!enabled) {
                updateProp('resourceNode.evaluation.endMessage', null)
                updateProp('resourceNode.evaluation.successMessage', null)
                updateProp('resourceNode.evaluation.failureMessage', null)
              }
            }
          }, {
            name: 'resourceNode.evaluation.endMessage',
            label: trans('end_message'),
            type: 'html',
            disabled: (data) => !enableMessages(data),
            /*options: {
              workspace: props.workspace
            }*/
          }, {
            name: 'resourceNode.evaluation.successMessage',
            label: trans('success_message'),
            type: 'html',
            disabled: (data) => !enableMessages(data),
            /*options: {
              workspace: props.workspace
            }*/
          }, {
            name: 'resourceNode.evaluation.failureMessage',
            label: trans('failure_message'),
            type: 'html',
            disabled: (data) => !enableMessages(data),
            /*options: {
              workspace: props.workspace
            }*/
          }, {
            name: 'resourceNode.evaluation.attemptsReachedMessage',
            label: trans('Message Tentatives épuisées'),
            type: 'html',
            disabled: (data) => !enableMessages(data),
            /*options: {
              workspace: props.workspace
            }*/
          }
        ]
      }
    ]}
  >
    {props.children}
  </EditorPage>

ResourceEditorEvaluation.propTypes = {
  children: T.any
}

export {
  ResourceEditorEvaluation
}
