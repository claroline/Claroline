import React, {useCallback} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {makeId} from '#/main/app/utils/id'
import {trans, transChoice} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {EditorPage} from '#/main/app/editor'
import {ContentSummary} from '#/main/app/content/components/summary'

import {actions, selectors} from '#/main/evaluation/sequence/editor/store'
import {getNumbering} from '#/main/evaluation/sequence/utils'
import {getFormDataPart} from '#/main/evaluation/sequence/editor/utils'
import {addStep, getStepActions} from '#/main/evaluation/sequence/editor/actions'

const SequenceEditorSummary = () => {
  const history = useHistory()
  const dispatch = useDispatch()

  const editorPath = useSelector(selectors.path)
  const errors = useSelector(selectors.errors)

  const baseNumbering = useSelector(selectors.numbering)
  const steps = useSelector(selectors.steps)
  const workspace = useSelector(selectors.workspace)

  const update = useCallback((steps) => dispatch(actions.update(steps, 'steps')), [editorPath])

  function getStepSummary(step) {
    return {
      id: step.id,
      type: LINK_BUTTON,
      numbering: getNumbering(baseNumbering, steps, step),
      label: (
        <div className="text-start" role="presentation">
          {step.title}

          {(!isEmpty(step.primaryResource) || !isEmpty(step.secondaryResources)) &&
            <small className="d-flex gap-2 text-body-secondary">
              {step.primaryResource &&
                <>
                  {trans('resource_name', {
                    type: trans(step.primaryResource.meta.type, {}, 'resource'),
                    name: step.primaryResource.name
                  }, 'resource')}

                  {!isEmpty(step.secondaryResources) &&
                    <span role="presentation">+</span>
                  }
                </>
              }

              {!isEmpty(step.secondaryResources) &&
                transChoice('count_resources', step.secondaryResources.length, {count: step.secondaryResources.length}, 'resource')
              }
            </small>
          }
        </div>
      ),
      target: `${editorPath}/steps/${step.slug}`,
      subscript: !isEmpty(get(errors, getFormDataPart(step.id, steps))) ? {
        type: 'text',
        status: 'danger',
        value: <span className="fa fa-fw fa-exclamation-circle" role="alert" />
      } : undefined,
      actions: getStepActions(
        steps,
        step,
        update,
        (path) => history.push(editorPath+path)
      ),
      children: step.children ? step.children.map(getStepSummary) : []
    }
  }

  return (
    <EditorPage
      title={trans('Scenario')}
      help={trans('Construisez votre scénario pédagogique en définissant vos objectifs d\'apprentissage et en ajoutant les différentes activités à effectuer par les utilisateurs.')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'overview.resource',
              type: 'resource',
              label: trans('resource'),
              help: trans('Ajoutez une ressource sur la page "A propos" de votre séquence pour introduire le contenu ou le travail à effectuer.'),
              options: {
                picker: {
                  contextId: get(workspace, 'id')
                }
              }
            }, {
              name: 'objective',
              label: trans('objective', {}, 'evaluation'),
              type: 'html',
              options: {
                workspace: workspace
              }
            }
          ]
        }
      ]}
    >
      <ContentSummary
        toolbar="add more"
        links={steps.map(getStepSummary)}
      />

      <Button
        type={CALLBACK_BUTTON}
        className={classes('btn btn-primary w-100', {
          'btn-wave': isEmpty(steps)
        })}
        label={trans('add_sequence_step', {}, 'actions')}
        size="lg"
        callback={() => {
          const newStepId = makeId()

          // update store
          update(addStep(steps, {id: newStepId}))
          // open new step
          history.push(`${editorPath}/steps/${newStepId}`)
        }}
      />
    </EditorPage>
  )
}

export {
  SequenceEditorSummary
}
