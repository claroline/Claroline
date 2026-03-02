import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {scrollTo} from '#/main/app/dom/scroll'
import {LINK_BUTTON} from '#/main/app/buttons'
import {EditorPage} from '#/main/app/editor'

import {selectors} from '#/main/evaluation/sequence/editor/store'
import {flattenSteps, getNext, getNumbering, getPrevious} from '#/main/evaluation/sequence/utils'
import {getFormDataPart} from '#/main/evaluation/sequence/editor/utils'

const SequenceEditorStep = props => {
  const editorPath = useSelector(selectors.path)
  const steps = useSelector(selectors.steps)
  const flatSteps = flattenSteps(steps)
  const step = flatSteps.find(s => props.match.params.slug === s.slug || props.match.params.slug === s.id)
  if (!step) {
    props.history.push(editorPath+'/steps')
  }

  const workspace = useSelector(selectors.workspace)
  const numbering = useSelector(selectors.numbering)
  const stepNumbering = getNumbering(numbering, steps, step)

  const next = getNext(flatSteps, step)
  const previous = getPrevious(flatSteps, step)

  return (
    <EditorPage
      title={(stepNumbering ? stepNumbering + ' ' + step.title : step.title) || trans('step', {}, 'path')}
      dataPart={getFormDataPart(step.id, steps)}
      actions={[
        {
          name: 'summary',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-list',
          label: trans('open-summary', {}, 'actions'),
          target: editorPath+'/steps',
          exact: true,
          onClick: () => scrollTo('.app-editor-body')
        }
      ].concat([
        {
          name: 'previous',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-chevron-up',
          label: previous ? trans('previous_step', {title: previous.title}, 'evaluation') : trans('summary'),
          target: editorPath+'/steps/' + (previous ? previous.slug : ''),
          exact: true,
          onClick: () => scrollTo('.app-editor-body')
        }, {
          name: 'next',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-chevron-down',
          label: trans('next_step', {title: next ? next.title : trans('none')}, 'evaluation'),
          target: editorPath+'/steps/' + (next ? next.slug : ''),
          disabled: !next,
          onClick: () => scrollTo('.app-editor-body')
        }
      ])}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'poster',
              type: 'poster',
              hideLabel: true,
              label: trans('poster')
            }, {
              name: 'title',
              type: 'string',
              label: trans('title'),
              required: true
            }
          ]
        }, {
          title: trans('activity'),
          description: trans('Choisissez l\'activité à réaliser dans le cadre de cette étape.'),
          primary: true,
          fields: [
            {
              name: 'primaryResource',
              type: 'resource',
              label: trans('resource'),
              hideLabel: true,
              options: {
                embedded: true,
                picker: {
                  contextId: workspace.id
                }
              }
            }, {
              name: 'evaluation.required',
              type: 'boolean',
              label: trans('Rendre l\'étape obligatoire', {}, 'evaluation'),
              help: trans('Les utilisateurs devront terminer l\'activité pour progresser dans la séquence.', {}, 'evaluation')
            }, {
              name: 'evaluation.scored',
              type: 'boolean',
              label: trans('Utiliser le score de l\'activité', {}, 'evaluation'),
              help: trans('Le score obtenu par les utilisateurs à la fin de l\'activité sera utilisé dans le calcul du score de la séquence.', {}, 'evaluation')
            }
          ]
        }, {
          title: trans('further_information'),
          description: trans('further_information_help'),
          primary: true,
          fields: [
            {
              name: 'description',
              type: 'html',
              label: trans('description'),
              help: trans('Décrivez le travail à réaliser et/ou les objectifs d\'apprentissage de l\'activité.'),
              recommended: true,
              options: {
                workspace: workspace
              }
            }, {
              name: 'objective',
              label: trans('objective', {}, 'evaluation'),
              type: 'html',
              options: {
                workspace: workspace
              }
            }, {
              name: 'secondaryResources',
              type: 'resource',
              label: trans('secondary_resources', {}, 'path'),
              help: trans('Ajoutez des liens vers les ressources qui peuvent être utiles à la réalisation de l\'activité.', {}, 'path'),
              options: {
                multiple: true,
                picker: {
                  contextId: workspace.id
                }
              }
            }
          ]
        }
      ]}
    />
  )
}

SequenceEditorStep.propTypes = {
  // from Route
  match: T.shape({
    params: T.shape({
      slug: T.string
    })
  }).isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

export {
  SequenceEditorStep
}
