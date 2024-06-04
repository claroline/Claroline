import React from 'react'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {EditorPage} from '#/main/app/editor'
import {ContentSummary} from '#/main/app/content/components/summary'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {MODAL_STEP_POSITION} from '#/plugin/path/resources/path/editor/modals/position'
import {selectors} from '#/plugin/path/resources/path/editor/store'
import {getNumbering} from '#/plugin/path/resources/path/utils'
import {getFormDataPart} from '#/plugin/path/resources/path/editor/utils'

const PathEditorSummary = props => {
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
      additional: [
        {
          name: 'add',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('step_add_child', {}, 'path'),
          callback: () => {
            const newSlug = props.addStep(steps, step)
            props.history.push(`${resourceEditorPath}/steps/${newSlug}`)
          }
        }, {
          name: 'copy',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-clone',
          label: trans('copy', {}, 'actions'),
          modal: [MODAL_STEP_POSITION, {
            icon: 'fa fa-fw fa-clone',
            title: trans('copy'),
            step: step,
            steps: props.steps,
            selectAction: (position) => ({
              type: CALLBACK_BUTTON,
              label: trans('copy', {}, 'actions'),
              callback: () => props.copyStep(step.id, position)
            })
          }]
        }, {
          name: 'move',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-arrows',
          label: trans('move', {}, 'actions'),
          modal: [MODAL_STEP_POSITION, {
            icon: 'fa fa-fw fa-arrows',
            title: trans('movement'),
            step: step,
            steps: props.steps,
            selectAction: (position) => ({
              type: CALLBACK_BUTTON,
              label: trans('move', {}, 'actions'),
              callback: () => props.moveStep(step.id, position)
            })
          }]
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash',
          label: trans('delete', {}, 'actions'),
          callback: () => {
            props.removeStep(step.id)
            if (`${resourceEditorPath}/steps/${step.slug}` === props.location.pathname) {
              props.history.push(`${resourceEditorPath}/steps`)
            }
          },
          confirm: {
            title: trans('deletion'),
            subtitle: step.title,
            message: trans('step_delete_confirm', {}, 'path')
          },
          dangerous: true
        }
      ],
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
          const newSlug = props.addStep(steps)
          props.history.push(`${resourceEditorPath}/steps/${newSlug}`)
        }}
      />
    </EditorPage>
  )
}

/*PathEditorSummary.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  location: T.shape({
    pathname: T.string.isRequired
  }).isRequired,
  addStep: T.func.isRequired,
  copyStep: T.func.isRequired,
  moveStep: T.func.isRequired,
  removeStep: T.func.isRequired
}*/

export {
  PathEditorSummary
}
