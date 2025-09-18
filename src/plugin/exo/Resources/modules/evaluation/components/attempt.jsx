import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {Helmet} from 'react-helmet'

import {theme} from '#/main/app/config/theme'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ResourceAttempt} from '#/main/evaluation/resource/prop-types'

import {Answer, Paper} from '#/plugin/exo/resources/quiz/papers/prop-types'
import {QuizEvaluationAttemptStep} from '#/plugin/exo/evaluation/components/step'
import {Step} from '#/plugin/exo/resources/quiz/prop-types'
import {registerDefaultItemTypes} from '#/plugin/exo/items/item-types'
import {registerDefaultContentItemTypes} from '#/plugin/exo/contents/utils'

registerDefaultItemTypes()
registerDefaultContentItemTypes()

const QuizEvaluationAttempt = (props) => {
  const [loaded, setLoaded] = useState(false)

  useEffect(() => {
    props.loadCurrentPaper(props.attempt.id).then(() => setLoaded(true))
  }, [props.attempt.id])

  useEffect(() => {
    if (props.showStatistics) {
      props.statistics(props.quizId)
    }
  }, [props.showStatistics])

  return (
    <>
      {!loaded &&
        <ContentLoader />
      }

      {loaded && props.steps && props.steps
        .filter(step => step.items && 0 < step.items.length)
        .map((step, index) =>
          <QuizEvaluationAttemptStep
            key={step.id}
            showTitle={props.showTitles}
            showQuestionTitles={props.showQuestionTitles}
            numberingType={props.numberingType}
            questionNumberingType={props.questionNumberingType}
            index={index}
            id={step.id}
            title={step.title}
            items={step.items}
            answers={props.answers}
            stats={props.stats}
            showScore={props.showScore}
            showExpectedAnswers={props.showExpectedAnswers}
            showStatistics={props.showStatistics}
          />
        )
      }

      <Helmet>
        <link
          key="claroline-distribution-plugin-exo-quiz-resource"
          rel="stylesheet"
          type="text/css"
          href={theme('claroline-distribution-plugin-exo-quiz-resource')}
        />
      </Helmet>
    </>
  )
}

QuizEvaluationAttempt.propTypes = {
  attempt: T.shape(
    ResourceAttempt.propTypes
  ).isRequired,

  quizId: T.string.isRequired,
  paper: T.shape(
    Paper.propTypes
  ),
  showTitles: T.bool,
  showQuestionTitles: T.bool,
  numberingType: T.string,
  questionNumberingType: T.string,
  showScore: T.bool.isRequired,
  showExpectedAnswers: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  stats: T.object,
  answers: T.arrayOf(T.shape(
    Answer.propTypes
  )),
  steps: T.arrayOf(T.shape(
    Step.propTypes
  )),
  loadCurrentPaper: T.func.isRequired,
  statistics: T.func.isRequired
}

export {
  QuizEvaluationAttempt
}
