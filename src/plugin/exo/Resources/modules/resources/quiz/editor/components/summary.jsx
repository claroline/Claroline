import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'
import {EditorPage} from '#/main/app/editor'

import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {ItemIcon} from '#/plugin/exo/items/components/icon'
import {getDefinition} from '#/plugin/exo/items/item-types'
import {calculateTotal} from '#/plugin/exo/items/score'
import {Badge} from '#/main/app/components/badge'

const QuizEditorSummary = props => {
  return (
    <EditorPage
      title={trans('steps', {}, 'quiz')}
    >
      <ContentSummary
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
          actions: props.getStepActions(step, index),
          children: (step.items || []).map((item, itemIndex) => {
            const itemDefinition = getDefinition(item.type)
            let totalScore
            if (item.hasExpectedAnswers) {
              totalScore = calculateTotal(item)
            }

            return ({
              id: item.id,
              type: LINK_BUTTON,
              numbering: getNumbering(props.questionNumberingType, index, itemIndex),
              label: (
                <>
                  <div className="d-flex align-items-center align-self-center">
                    <ItemIcon name={itemDefinition.name} size="xs" className="my-n2" />
                    {item.title || trans(itemDefinition.name, {}, 'question_types')}
                  </div>
                  {(totalScore || 0 === totalScore) ?
                    <Badge variant="primary" subtle={true} className="ms-auto me-n4">
                      {transChoice('solution_score', totalScore, {score: totalScore}, 'quiz')}
                    </Badge> :
                    <Badge variant="secondary" subtle={true} className="ms-auto me-n4">
                      {trans('score_none', {}, 'quiz')}
                    </Badge>
                  }
                </>
              ),
              target: `${props.path}/steps/${step.slug}/${item.id}`,
              subscript: !isEmpty(get(props.errors, `resource.steps[${index}].items[${itemIndex}]`)) ? {
                type: 'text',
                status: 'danger',
                value: <span className="fa fa-fw fa-exclamation-circle" />
              } : undefined,
              actions: props.getItemActions(step, index, item, itemIndex)
            })
          })
        }))}
      />

      <Button
        type={CALLBACK_BUTTON}
        className="btn btn-primary w-100"
        size="lg"
        label={trans('step_add', {}, 'path')}
        callback={props.addStep}
      />
    </EditorPage>
  )
}

QuizEditorSummary.propTypes = {
  path: T.string.isRequired,
  numberingType: T.string,
  questionNumberingType: T.string,
  steps: T.arrayOf(T.shape({
    // step types
  })),
  errors: T.object,
  getStepActions: T.func.isRequired,
  getItemActions: T.func.isRequired,
  addStep: T.func.isRequired
}

QuizEditorSummary.defaultProps = {
  steps: []
}

export {
  QuizEditorSummary
}
