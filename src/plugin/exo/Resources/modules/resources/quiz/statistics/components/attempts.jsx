import React from 'react'
import {PropTypes as T} from 'prop-types'

import {AttemptsChart} from '#/plugin/exo/charts/attempts/components/chart'
import {PageSection} from '#/main/app/page'

const AttemptsStats = (props) =>
  <PageSection size="full">
    <AttemptsChart
      quizId={props.quizId}
      steps={props.steps}
      questionNumberingType={props.questionNumberingType}
    />
  </PageSection>

AttemptsStats.propTypes = {
  quizId: T.string.isRequired,
  steps: T.array,
  questionNumberingType: T.string.isRequired
}

export {
  AttemptsStats
}
