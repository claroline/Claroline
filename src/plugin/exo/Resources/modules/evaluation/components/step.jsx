import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentTitle} from '#/main/app/content/components/title'
import {isQuestionType} from '#/plugin/exo/items/item-types'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {Item as ItemTypes} from '#/plugin/exo/items/prop-types'
import {ItemResult} from '#/plugin/exo/items/components/result'

function getAnswer(itemId, answers) {
  return answers.find(answer => answer.questionId === itemId)
}

function getStats(itemId, stats = {}) {
  return stats[itemId] ? stats[itemId] : {}
}

const QuizEvaluationAttemptStep = props => {
  const numbering = getNumbering(props.numberingType, props.index)

  return (
    <>
      {props.showTitle &&
        <ContentTitle
          level={4}
          displayLevel={3}
          numbering={numbering}
          title={props.title || trans('step', {number: props.index + 1}, 'quiz')}
        />
      }

      {props.items
        .filter((item) => isQuestionType(item.type))
        .map((item, idxItem) =>
          <ItemResult
            key={item.id}
            item={item}
            numbering={getNumbering(props.questionNumberingType, props.index, idxItem)}
            userAnswer={getAnswer(item.id, props.answers)}
            stats={getStats(item.id, props.stats)}
            showTitle={props.showQuestionTitles}
            showScore={props.showScore}
            showExpectedAnswers={props.showExpectedAnswers}
            showStatistics={props.showStatistics}
          />
        )
      }
    </>
  )
}

QuizEvaluationAttemptStep.propTypes = {
  numberingType: T.string.isRequired,
  questionNumberingType: T.string.isRequired,
  showTitle: T.bool,
  showQuestionTitles: T.bool,
  index: T.number.isRequired,
  id: T.string.isRequired,
  title: T.string,
  items: T.arrayOf(T.shape(
    ItemTypes.propTypes
  )),
  showScore: T.bool.isRequired,
  showExpectedAnswers: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  answers: T.array,
  stats: T.object
}

export {
  QuizEvaluationAttemptStep
}
