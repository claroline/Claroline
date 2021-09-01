import React from 'react'
import {PropTypes as T} from 'prop-types'

import {AttemptsChart} from '#/plugin/exo/charts/attempts/components/chart'

const AttemptsStats = (props) =>
  <AttemptsChart
    quizId={props.quizId}
    steps={props.steps}
    questionNumberingType={props.questionNumberingType}
  />

AttemptsStats.propTypes = {
  quizId: T.string.isRequired,
  steps: T.array,
  questionNumberingType: T.string.isRequired
}

export {
  AttemptsStats
}
