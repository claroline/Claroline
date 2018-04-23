import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {tex} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {select as resourceSelect} from '#/main/core/resource/selectors'
import {select as playerSelectors} from './../selectors'
import quizSelectors from './../../selectors'
import {selectors as paperSelectors} from './../../papers/selectors'
import {utils as paperUtils} from './../../papers/utils'
import {ScoreGauge} from './../../../components/score-gauge.jsx'

const AttemptEnd = props => {
  const showScore = paperUtils.showScore(props.admin, props.paper.finished, paperSelectors.showScoreAt(props.paper), paperSelectors.showCorrectionAt(props.paper), paperSelectors.correctionDate(props.paper))
  const showCorrection = paperUtils.showCorrection(props.admin, props.paper.finished, paperSelectors.showCorrectionAt(props.paper), paperSelectors.correctionDate(props.paper))
  const answers = Object.keys(props.answers).map(key => props.answers[key])

  return (
    <div className="quiz-player attempt-end">
      <div className="row">
        {showScore &&
          <div className="col-md-3 text-center">
            <ScoreGauge userScore={paperUtils.computeScore(props.paper, answers)} maxScore={paperSelectors.paperScoreMax(props.paper)} />
          </div>
        }

        <div className={showScore ? 'col-md-9':'col-md-12'}>
          {props.endMessage ?
            <HtmlText>{props.endMessage}</HtmlText> :
            <div>
              <h2 className="h4">{tex('attempt_end_title')}</h2>
              <p>{tex('attempt_end_info')}</p>
            </div>
          }

          {props.endNavigation && showCorrection &&
            <Button
              type="link"
              className="btn btn-start btn-lg btn-block btn-primary"
              icon="fa fa-fw fa-play"
              label={tex('view_paper')}
              target={`/papers/${props.paper.id}`}
            />
          }

          {props.endNavigation &&
            <Button
              type="link"
              className="btn btn-start btn-lg btn-block btn-primary"
              icon="fa fa-fw fa-play"
              label={tex('exercise_restart')}
              target="/play"
            />
          }
        </div>
      </div>
    </div>
  )
}

AttemptEnd.propTypes = {
  admin: T.bool.isRequired,
  answers: T.object.isRequired,
  paper: T.shape({
    id: T.string.isRequired,
    structure: T.object.isRequired,
    finished: T.bool.isRequired
  }).isRequired,
  endMessage: T.string,
  endNavigation: T.bool.isRequired
}

const ConnectedAttemptEnd = connect(
  (state) => ({
    admin: resourceSelect.editable(state) || quizSelectors.papersAdmin(state),
    paper: playerSelectors.paper(state),
    endMessage: playerSelectors.quizEndMessage(state),
    endNavigation: playerSelectors.quizEndNavigation(state),
    answers: playerSelectors.answers(state)
  })
)(AttemptEnd)

export {
  ConnectedAttemptEnd as AttemptEnd
}
