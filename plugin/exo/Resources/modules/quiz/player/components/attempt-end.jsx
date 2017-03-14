import React, {PropTypes as T} from 'react'
import {connect} from 'react-redux'

import {tex} from './../../../utils/translate'
import {select as playerSelectors} from './../selectors'
import {selectors as paperSelectors} from './../../papers/selectors'
import {ScoreGauge} from './../../../components/score-gauge.jsx'

const AttemptEnd = props =>
  <div className="quiz-player attempt-end">
    <div className="row">
      <div className="col-md-3 text-center">
        <ScoreGauge userScore={props.paper.score} maxScore={paperSelectors.paperScoreMax(props.paper)} />
      </div>

      <div className="col-md-9">
        <h2 className="step-title">{tex('attempt_end_title')}</h2>
        <p>{tex('attempt_end_info')}</p>

        <a href="#play" className="btn btn-start btn-lg btn-block btn-primary">
          {tex('exercise_restart')}
        </a>
      </div>
    </div>
  </div>

AttemptEnd.propTypes = {
  paper: T.shape({
    id: T.string.isRequired,
    score: T.number
  }).isRequired
}

function mapStateToProps(state) {
  return {
    paper: playerSelectors.paper(state)
  }
}

const ConnectedAttemptEnd = connect(mapStateToProps)(AttemptEnd)

export {ConnectedAttemptEnd as AttemptEnd}
