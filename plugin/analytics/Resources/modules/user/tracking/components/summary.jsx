import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'

const Summary = props =>
  <div className="panel panel-default">
    <div className="panel-body">
      <div>
        <b>{trans('count_resources')}</b> : {props.evaluations.length}
      </div>
      <div>
        <b>{trans('total_time')}</b> : {
          props.evaluations.reduce((acc, evaluation) => {
            const duration = evaluation.duration ? evaluation.duration : 0

            return acc + duration
          }, 0)
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
