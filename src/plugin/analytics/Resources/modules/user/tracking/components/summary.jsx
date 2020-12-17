import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans, displayDuration} from '#/main/app/intl'
import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'

const Summary = props =>
  <div className="panel panel-default">
    <div className="panel-body">
      <div>
        <b>{trans('count_resources')}</b> : {props.evaluations.length}
      </div>
      <div>
        <b>{trans('total_time')}</b> : {
          displayDuration(props.evaluations.reduce((acc, evaluation) => acc + (evaluation.duration || 0), 0))
        }
      </div>
    </div>
  </div>

Summary.propTypes = {
  evaluations: T.arrayOf(T.shape(
    UserEvaluationTypes.propTypes
  ))
}

export {
  Summary
}
