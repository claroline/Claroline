import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'
import {EditorPage} from '#/main/app/editor'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'

const QuizEditorSummary = props => {
  return (
    <EditorPage
      title={trans('steps', {}, 'quiz')}
    >
      <ContentSummary
        noCollapse={true}
        links={props.steps.map((step, index) => ({
          id: step.id,
          type: LINK_BUTTON,
          numbering: getNumbering(props.numberingType, index),
          label: step.title || trans('step', {number: index + 1}, 'quiz'),
          target: `${props.path}/steps/${step.slug}`,
          subscript: !isEmpty(get(props.errors, `resource.steps[${index}]`)) ? {
            type: 'text',
            status: 'danger',
            value: <span className="fa fa-fw fa-exclamation-circle" />
          } : undefined,
          additional: props.getStepActions(step, index),
          children: (step.items || []).map((item, itemIndex) => ({
            id: item.id,
            type: LINK_BUTTON,
            numbering: getNumbering(props.questionNumberingType, index, itemIndex),
            label: item.title || trans(item.type, {}, 'question_types'),
            target: `${props.path}/steps/${step.slug}/${item.id}`,
            subscript: !isEmpty(get(props.errors, `resource.steps[${index}].items[${itemIndex}]`)) ? {
              type: 'text',
              status: props.validating ? 'danger' : 'warning',
              value: <span className="fa fa-fw fa-exclamation-circle" />
            } : undefined
          }))
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
  )
}

QuizEditorSummary.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  location: T.shape({
    pathname: T.string.isRequired
  }).isRequired,
  path: T.string.isRequired,
  numberingType: T.string.isRequired,
  questionNumberingType: T.string.isRequired,
  steps: T.arrayOf(T.shape({
    // step types
  })),
  errors: T.object,
  validating: T.bool.isRequired,
  getStepActions: T.func.isRequired,
  addStep: T.func.isRequired
}

QuizEditorSummary.defaultProps = {
  steps: []
}

export {
  QuizEditorSummary
}