import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {isHtmlEmpty} from '#/main/app/data/types/html/validators'
import {Html} from '#/main/app/components/html'
import {EvaluationScore} from '#/main/evaluation/components/score'

import {Item as ItemTypes} from '#/plugin/exo/items/prop-types'
import ScoreNone from '#/plugin/exo/scores/none'
import {calculateTotal} from '#/plugin/exo/items/score'
import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'
import {getComponent} from '#/plugin/exo/items/item-types'
import {PaperTabs} from '#/plugin/exo/items/components/paper-tabs'

const ItemResult = ({
  item,
  numbering,
  stats,
  userAnswer = {},
  showTitle = false,
  showScore = false,
  showExpectedAnswers = false,
  showStatistics = false
}) => {
  const userAnswerComponent = createElement(getComponent(item.type, 'feedback'), {
    item: item,
    answer: userAnswer && userAnswer.data ? userAnswer.data : undefined,
    feedback: userAnswer && userAnswer.feedback ? userAnswer.feedback : undefined,
    showScore: item.hasExpectedAnswers && ScoreNone.name !== get(item, 'score.type') && showScore
  })

  let expectedAnswerComponent
  if (getComponent(item.type, 'expectedAnswer')) {
    expectedAnswerComponent = createElement(getComponent(item.type, 'expectedAnswer'), {
      item: item,
      showScore: item.hasExpectedAnswers && ScoreNone.name !== get(item, 'score.type') && showScore
    })
  }

  let statsComponent
  if (getComponent(item.type, 'stats')) {
    statsComponent = createElement(getComponent(item.type, 'stats'), {
      item: item,
      stats: stats || {}
    })
  }

  return (
    <div key={item.id} className="card mb-3 quiz-item item-paper">
      <div className="card-body">
        {showScore && item.hasExpectedAnswers && ScoreNone.name !== get(item, 'score.type') && userAnswer.score !== undefined && userAnswer.score !== null &&
          <EvaluationScore className="pull-right" score={userAnswer.score} scoreMax={calculateTotal(item)}/>
        }

        <ItemMetadata
          item={item}
          showTitle={showTitle}
          numbering={numbering}
        />

        <PaperTabs
          id={item.id}
          showExpected={showExpectedAnswers && item.hasExpectedAnswers}
          showStats={showStatistics && !!stats}
          showYours={true}
          yours={userAnswerComponent}
          expected={expectedAnswerComponent}
          stats={statsComponent}
        />

        {(item.feedback && !isHtmlEmpty(item.feedback)) &&
          <div className="item-feedback">
            <span className="fa fa-comment" />
            <Html>{item.feedback}</Html>
          </div>
        }
      </div>
    </div>
  )
}

ItemResult.propTypes = {
  item: T.shape(
    ItemTypes.propTypes
  ).isRequired,
  numbering: T.string,
  userAnswer: T.shape({
    score: T.number,
    feedback: T.string,
    usedHints: T.array,
    data: T.any
  }),
  stats: T.any,
  showTitle: T.bool.isRequired,
  showScore: T.bool.isRequired,
  showExpectedAnswers: T.bool.isRequired,
  showStatistics: T.bool.isRequired
}

export {
  ItemResult
}
