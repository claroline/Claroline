import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {EditorPage} from '#/main/app/editor'
import {ContentSummary} from '#/main/app/content/components/summary'
import {selectors as editorSelectors, actions as editorActions} from '#/main/core/resource/editor'

import {selectors} from '#/plugin/path/resources/path/editor/store'
import {getNumbering} from '#/plugin/path/resources/path/utils'
import {getFormDataPart} from '#/plugin/path/resources/path/editor/utils'
import {addStep, getStepActions} from '#/plugin/path/resources/path/editor/actions'

const PathEditorSummary = props => {
  const history = useHistory()
  const dispatch = useDispatch()
  const update = (steps) => dispatch(editorActions.updateResource(steps, 'steps'))

  const resourceEditorPath = useSelector(editorSelectors.path)
  const errors = useSelector(editorSelectors.errors)

  const baseNumbering = useSelector(selectors.numbering)
  const steps = useSelector(selectors.steps)

  function getStepSummary(step) {
    return {
      type: LINK_BUTTON,
      numbering: getNumbering(baseNumbering, steps, step),
      label: step.title,
      target: `${resourceEditorPath}/steps/${step.slug}`,
      subscript: !isEmpty(get(errors, `resource.${getFormDataPart(step.id, steps)}`)) ? {
        type: 'text',
        status: 'danger',
        value: <span className="fa fa-fw fa-exclamation-circle" />
      } : undefined,
      additional: getStepActions(
        steps,
        step,
        update,
        (path) => history.push(resourceEditorPath+path)
      ),
      children: step.children ? step.children.map(getStepSummary) : []
    }
  }

  return (
    <EditorPage
      title={trans('Scenario')}
      help={trans('Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?')}
      definition={[
        {
          name: 'general',
          title: trans('general'),
          fields: [
            {
              name: 'objectives',
              label: trans('Objectif d\'apprentissage'),
              type: 'html',
              required: true
            }, {
              name: 'requirements',
              type: 'boolean',
              label: trans('Ajouter des pré-requis'),
              help: trans('Les utilisateurs ne pourront faire cette séquence qu\'une fois les séquences requises terminées.'),
            }
          ]
        }
      ]}
    >
      <ContentSummary
        links={steps.map(getStepSummary)}
        noCollapse={true}
      />

      <Button
        type={CALLBACK_BUTTON}
        className={classes('btn btn-primary w-100 mt-3', {
          'btn-wave': isEmpty(steps)
        })}
        label={trans('step_add', {}, 'path')}
        size="lg"
        callback={() => {
          const newStepId = makeId()

          // update store
          update(addStep(steps, {id: newStepId}))
          // open new step
          history.push(`${resourceEditorPath}/steps/${newStepId}`)
        }}
      />
    </EditorPage>
  )
}

export {
  PathEditorSummary
}
