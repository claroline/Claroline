import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'
import Panel from 'react-bootstrap/lib/Panel'

import {tex} from '#/main/app/intl/translation'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'
import {ScoreGauge} from '#/main/core/layout/evaluation/components/score-gauge'

import quizSelect from '#/plugin/exo/quiz/selectors'
import {constants} from '#/plugin/exo/resources/quiz/constants'
import {getDefinition, isQuestionType} from '#/plugin/exo/items/item-types'
import {selectors as paperSelect} from '#/plugin/exo/quiz/papers/selectors'
import {utils} from '#/plugin/exo/quiz/papers/utils'
import {getNumbering} from '#/plugin/exo/utils/numbering'
import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'

function getAnswer(itemId, answers) {
  const answer = answers.find(answer => answer.questionId === itemId)

  return answer && answer.data ? answer.data : undefined
}

function getAnswerFeedback(itemId, answers) {
  const answer = answers.find(answer => answer.questionId === itemId)

  return answer && answer.feedback ? answer.feedback : null
}

function getAnswerScore(itemId, answers) {
  const answer = answers.find(answer => answer.questionId === itemId)

  return answer ? answer.score : undefined
}

const PaperComponent = props => {
  const showScore = props.paper ?
    utils.showScore(
      props.admin,
      props.paper.finished,
      props.paper.structure.parameters.showScoreAt,
      props.paper.structure.parameters.showCorrectionAt,
      props.paper.structure.parameters.correctionDate
    ) :
    false

  return (
    <div className="paper">
      <h2 className="paper-title">
        {showScore &&
          <ScoreGauge userScore={props.paper.score} maxScore={paperSelect.paperScoreMax(props.paper)} size="sm" />
        }
        {tex('correction')}&nbsp;{props.paper ? props.paper.number : ''}
      </h2>

      {props.paper && props.paper.structure.steps.map((step, idx) =>
        <div key={idx} className="quiz-item item-paper">
          <h3 className={classes('h4', 0 === idx && 'h-first')}>
            {step.title ? step.title : tex('step') + ' ' + (idx + 1)}
          </h3>

          {step.items.map((item, idxItem) => {
            const tmp = document.createElement('div')
            tmp.innerHTML = item.feedback
            const displayFeedback = (/\S/.test(tmp.textContent)) && item.feedback

            return isQuestionType(item.type) ?
              <Panel key={item.id}>
                {showScore && getAnswerScore(item.id, props.paper.answers) !== undefined && getAnswerScore(item.id, props.paper.answers) !== null &&
                  <ScoreBox className="pull-right" score={getAnswerScore(item.id, props.paper.answers)} scoreMax={paperSelect.itemScoreMax(item)}/>
                }
                {item.title &&
                  <h4 className="item-title">{item.title}</h4>
                }

                <ItemMetadata item={item} numbering={props.numbering !== constants.NUMBERING_NONE ? (idx + 1) + '.' + getNumbering(props.numbering, idxItem): null} />

                {React.createElement(
                  getDefinition(item.type).paper,
                  {
                    item, answer: getAnswer(item.id, props.paper.answers),
                    feedback: getAnswerFeedback(item.id, props.paper.answers),
                    showScore: showScore,
                    showExpected: props.showExpectedAnswers,
                    showStats: !!(props.showStatistics && props.stats && props.stats[item.id]),
                    showYours: true,
                    stats: props.showStatistics && props.stats && props.stats[item.id] ? props.stats[item.id] : {}
                  }
                )}

                {displayFeedback &&
                  <div className="item-feedback">
                    <span className="fa fa-comment" />
                    <div dangerouslySetInnerHTML={{__html: item.feedback}} />
                  </div>
                }
              </Panel>
              :
              ''
          })}
        </div>
      )}
    </div>
  )
}

PaperComponent.propTypes = {
  admin: T.bool.isRequired,
  paper: T.shape({
    id: T.string.isRequired,
    number: T.number.isRequired,
    score: T.number,
    finished: T.bool.isRequired,
    structure: T.object.isRequired,
    answers: T.array
  }),
  numbering: T.string,
  showExpectedAnswers: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  stats: T.object
}

const Paper = connect(
  (state) => ({
    admin: hasPermission('edit', resourceSelect.resourceNode(state)) || quizSelect.papersAdmin(state),
    numbering: quizSelect.quizNumbering(state),
    paper: paperSelect.currentPaper(state),
    showExpectedAnswers: quizSelect.papersShowExpectedAnswers(state),
    showStatistics: quizSelect.papersShowStatistics(state),
    stats: quizSelect.statistics(state)
  })
)(PaperComponent)

export {
  Paper
}
