import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {EditorPage} from '#/main/app/editor'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {actions as editorActions, selectors as editorSelectors} from '#/main/core/resource/editor'

import {selectors} from '#/plugin/path/resources/path/editor/store'
import {flattenSteps, getNumbering} from '#/plugin/path/resources/path/utils'
import {getFormDataPart} from '#/plugin/path/resources/path/editor/utils'
import {getStepActions} from '#/plugin/path/resources/path/editor/actions'
import {PathNav} from '#/plugin/path/resources/path/components/nav'
import {scrollTo} from '#/main/app/dom/scroll'

const PathEditorStep = props => {
  const dispatch = useDispatch()
  const update = (steps) => dispatch(editorActions.updateResource(steps, 'steps'))

  const resourceEditorPath = useSelector(editorSelectors.path)
  const steps = useSelector(selectors.steps)
  const flatSteps = flattenSteps(steps)
  const step = flatSteps.find(s => props.match.params.slug === s.slug || props.match.params.slug === s.id)
  if (!step) {
    props.history.push(resourceEditorPath+'/steps')
  }

  const workspaceId = useSelector(resourceSelectors.workspaceId)
  const hasCustomNumbering = useSelector(selectors.hasCustomNumbering)
  const numbering = useSelector(selectors.numbering)
  const stepNumbering = getNumbering(numbering, steps, step)

  const actions = getStepActions(
    steps,
    step,
    update,
    (path) => props.history.push(resourceEditorPath+path),
    true
  )

  return (
    <EditorPage
      title={
        <>
          {stepNumbering &&
            <span className="h-numbering">{stepNumbering}</span>
          }

          {step.title || trans('step', {}, 'path')}
        </>
      }
      dataPart={'resource.'+getFormDataPart(step.id, steps)}
      actions={[
        {
          name: 'summary',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-list',
          label: trans('open-summary', {}, 'actions'),
          target: resourceEditorPath+'/steps',
          exact: true
        }
      ].concat(actions)}
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
              autoFocus: true,
              required: true
            }
          ]
        }, {
          title: trans('Activité'),
          subtitle: trans('Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?'),
          primary: true,
          fields: [
            {
              name: 'primaryResource',
              type: 'resource',
              label: trans('resource'),
              hideLabel: true,
              options: {
                embedded: true,
                showHeader: true,
                picker: {
                  contextId: workspaceId
                }
              },
              linked: [
                {
                  name: 'showResourceHeader',
                  type: 'boolean',
                  label: trans('show_resource_header', {}, 'resource'),
                  displayed: (step) => !isEmpty(step.primaryResource)
                }
              ]
            }, {
              name: '_enableSecondaryResources',
              type: 'boolean',
              label: trans('Ajouter des ressources complémentaires', {}, 'path'),
              help: trans('Ajoutez des liens vers les ressources qui peuvent être utiles à la réalisation de l\'activité.', {}, 'path'),
              calculated: (step) => step._enableSecondaryResources || !isEmpty(step.secondaryResources),
              linked: [
                {
                  name: 'secondaryResources',
                  type: 'resources',
                  label: trans('secondary_resources', {}, 'path'),
                  displayed: (step) => step._enableSecondaryResources || !isEmpty(step.secondaryResources),
                  options: {
                    picker: {
                      contextId: workspaceId
                    }
                  }
                }
              ]
            }
          ]
        }, {
          title: trans('further_information'),
          subtitle: trans('further_information_help'),
          primary: true,
          fields: [
            {
              name: 'display.numbering',
              type: 'string',
              label: trans('step_numbering', {}, 'path'),
              displayed: hasCustomNumbering
            }, {
              name: 'description',
              type: 'html',
              label: trans('description'),
              options: {
                workspace: props.workspace
              }
            }, {
              name: 'poster',
              type: 'image',
              label: trans('poster'),
            }
          ]
        }
      ]}
    >
      <PathNav
        current={step}
        all={flatSteps}
        path={resourceEditorPath+'/steps'}
        onNavigate={() => scrollTo(`.app-editor-form`)}
      />
    </EditorPage>
  )
}

PathEditorStep.propTypes = {
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
  PathEditorStep
}
