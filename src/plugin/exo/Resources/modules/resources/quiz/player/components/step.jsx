import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {isQuestionType} from '#/plugin/exo/items/item-types'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {ItemPlayer} from '#/plugin/exo/items/components/player'
import {ItemFeedback} from '#/plugin/exo/items/components/feedback'
import {ContentItemPlayer} from '#/plugin/exo/contents/components/content-item-player'
import {Html} from '#/main/app/components/html'
import {NUMBERING_NONE} from '#/main/app/utils/numbering'

const PlayerStep = props => {
  const numbering = getNumbering(props.numbering, props.number - 1)

  return (
    <section className="current-step">
      {props.showTitle &&
        <h3 className="h2 step-title">
          {numbering &&
            <span className="h-numbering">{numbering}</span>
          }

          {props.step.title || trans('step', {number: props.number}, 'quiz')}
        </h3>
      }

      {props.step.description &&
        <Html className="content-text mb-3 lead">{props.step.description}</Html>
      }

      {props.items.map((item, index) => (
        <div className="card mb-4" key={item.id}>
          <div className="card-body">
            {!isQuestionType(item.type) ?
              <ContentItemPlayer
                id={`item-${item.id}`}
                showTitle={props.showQuestionTitles}
                item={item}
              />
              : (!props.feedbackEnabled ?
                <ItemPlayer
                  item={item}
                  showHint={props.showHint}
                  usedHints={props.answers[item.id] ? props.answers[item.id].usedHints : []}
                  showTitle={props.showQuestionTitles}
                  numbering={getNumbering(props.questionNumbering, props.number - 1, index)}
                  answer={props.answers[item.id]}
                  editable={!props.answersEditable}
                  updateAnswer={props.updateAnswer}
                />
                :
                <ItemFeedback
                  item={item}
                  usedHints={props.answers[item.id] ? props.answers[item.id].usedHints : []}
                  showTitle={props.showQuestionTitles}
                  numbering={props.questionNumbering !== NUMBERING_NONE ? props.number + '.' + getNumbering(props.questionNumbering, index): null}
                  answer={props.answers[item.id]}
                />
              )}
          </div>
        </div>
      ))}
    </section>
  )
}

PlayerStep.propTypes = {
  numbering: T.string,
  questionNumbering: T.string,
  number: T.number.isRequired,
  showTitle: T.bool,
  showQuestionTitles: T.bool,
  step: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string
  }).isRequired,
  items: T.array.isRequired,
  answers: T.object.isRequired,
  feedbackEnabled: T.bool.isRequired,
  answersEditable: T.bool.isRequired,

  updateAnswer: T.func.isRequired,
  showHint: T.func.isRequired
}

export {
  PlayerStep
}
