import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'

import {MODAL_STEP_POSITION} from '#/plugin/exo/resources/quiz/editor/modals/step-position'
import {EditorPage} from '#/main/app/editor'
import {Button} from '#/main/app/action'

const QuizEditorSummary = props =>
  <EditorPage
    title={trans('steps', {}, 'quiz')}
  >
    <ContentSummary
      links={props.steps.map((step, index) => ({
        type: LINK_BUTTON,
        label: step.title || trans('step', {number: index + 1}, 'quiz'),
        target: `${props.path}/steps/${step.slug}`,
        subscript: !isEmpty(get(props.errors, `steps[${index}]`)) ? {
          type: 'text',
          status: props.validating ? 'danger' : 'warning',
          value: <span className="fa fa-fw fa-exclamation-circle" />
        } : undefined,
        additional: [
          {
            name: 'copy',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-clone',
            label: trans('copy', {}, 'actions'),
            modal: [MODAL_STEP_POSITION, {
              icon: 'fa fa-fw fa-clone',
              title: trans('copy'),
              step: {
                id: step.id,
                title: step.title || trans('step', {number: index + 1}, 'quiz')
              },
              steps: props.steps.map((s, i) => ({
                id: s.id,
                title: s.title || trans('step', {number: i + 1}, 'quiz')
              })),
              selectAction: (position) => ({
                type: CALLBACK_BUTTON,
                label: trans('copy', {}, 'actions'),
                callback: () => props.copyStep(step.id, props.steps, position)
              })
            }],
            group: trans('management')
          }, {
            name: 'move',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-arrows',
            label: trans('move', {}, 'actions'),
            modal: [MODAL_STEP_POSITION, {
              icon: 'fa fa-fw fa-arrows',
              title: trans('movement'),
              step: {
                id: step.id,
                title: step.title || trans('step', {number: index + 1}, 'quiz')
              },
              steps: props.steps.map((s, i) => ({
                id: s.id,
                title: s.title || trans('step', {number: i + 1}, 'quiz')
              })),
              selectAction: (position) => ({
                type: CALLBACK_BUTTON,
                label: trans('move', {}, 'actions'),
                callback: () => props.moveStep(step.id, position)
              })
            }],
            group: trans('management')
          }, {
            name: 'delete',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash',
            label: trans('delete', {}, 'actions'),
            callback: () => props.removeStep(step.id),
            confirm: {
              title: trans('deletion'),
              subtitle: step.title || trans('step', {number: index + 1}, 'quiz'),
              message: trans('remove_step_confirm_message', {}, 'quiz')
            },
            dangerous: true,
            group: trans('management')
          }
        ]
      }))}
    />

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-primary w-100 mt-3"
      size="lg"
      label={trans('step_add', {}, 'path')}
      callback={() => {
        const newSlug = props.addStep(props.steps)
        props.history.push(`${props.path}/steps/${newSlug}`)
      }}
    />
  </EditorPage>

QuizEditorSummary.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  location: T.shape({
    pathname: T.string.isRequired
  }).isRequired,
  path: T.string.isRequired,
  steps: T.arrayOf(T.shape({
    // TODO : step types
  })),
  errors: T.object,
  validating: T.bool.isRequired,
  addStep: T.func.isRequired,
  copyStep: T.func.isRequired,
  moveStep: T.func.isRequired,
  removeStep: T.func.isRequired
}

QuizEditorSummary.defaultProps = {
  steps: []
}

export {
  QuizEditorSummary
}
