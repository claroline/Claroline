import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'
import Panel from 'react-bootstrap/lib/Panel'

import {tex} from '#/main/core/translation'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box.jsx'

import quizSelect from '#/plugin/exo/quiz/selectors'
import {
  NUMBERING_NONE
} from '#/plugin/exo/quiz/enums'
import {getDefinition, isQuestionType} from '#/plugin/exo/items/item-types'
import {selectors as paperSelect} from '#/plugin/exo/quiz/papers/selectors'
import {utils} from '#/plugin/exo/quiz/papers/utils'
import {getNumbering} from '#/plugin/exo/utils/numbering'
import {ScoreGauge} from '#/plugin/exo/components/score-gauge.jsx'
import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata.jsx'

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

                <ItemMetadata item={item} numbering={props.numbering !== NUMBERING_NONE ? (idx + 1) + '.' + getNumbering(props.numbering, idxItem): null} />

                {React.createElement(
                  getDefinition(item.type).paper,
                  {
                    item, answer: getAnswer(item.id, props.paper.answers),
                    feedback: getAnswerFeedback(item.id, props.paper.answers),
                    showScore: showScore,
                    showExpected: props.showExpectedAnswers,
                    showStats: props.showStatistics,
                    showYours: true,
                    stats: props.showStatistics ?
                      getDefinition(item.type).generateStats(item, props.papers, props.allPapersStatistics) :
                      {}
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
    structure: T.object.isRequired
  }),
  showExpectedAnswers: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  allPapersStatistics: T.bool.isRequired,
  papers: T.object.isRequired
}

const Paper = connect(
  (state) => ({
    admin: hasPermission('edit', resourceSelect.resourceNode(state)) || quizSelect.papersAdmin(state),
    numbering: quizSelect.quizNumbering(state),
    paper: paperSelect.currentPaper(state),
    showExpectedAnswers: quizSelect.papersShowExpectedAnswers(state),
    showStatistics: quizSelect.papersShowStatistics(state),
    allPapersStatistics: quizSelect.allPapersStatistics(state),
    papers: paperSelect.papers(state)
  })
)(PaperComponent)

export {
  Paper
}
