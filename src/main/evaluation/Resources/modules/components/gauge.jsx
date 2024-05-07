import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {arc} from 'd3-shape'
import {scaleLinear} from 'd3-scale'

import {constants} from '#/main/evaluation/constants'
import {EvaluationStatus} from '#/main/evaluation/components/status'
import {GaugeContainer} from '#/main/core/layout/gauge/components/gauge'

const EvaluationProgress = (props) => {
  const circleX = scaleLinear().range([-(2 * Math.PI) / 3, (2 * Math.PI) / 3]).domain([0, 100])

  const gutter =  arc()
    .startAngle(circleX(0))
    .endAngle(circleX(80))
    .outerRadius(70)
    .innerRadius(75)

  const progress =  arc()
    .startAngle(circleX(0))
    .endAngle(circleX(props.progression))
    .outerRadius(70)
    .innerRadius(75)

  const radius = 80

  return (
    <GaugeContainer type={props.type} width={160} height={160} radius={radius} >
      <path className="bg" d={gutter()} transform={`translate(${radius}, ${radius})`}/>

      {props.progression &&
        <path className="meter" d={progress()} transform={`translate(${radius}, ${radius})`}/>
      }
    </GaugeContainer>
  )
}

EvaluationProgress.propTypes = {
  progression: T.number,
  type: T.string
}

const EvaluationGauge = (props) =>
  <div className={classes('evaluation-gauge', props.size && `evaluation-gauge-${props.size}`, props.className, constants.EVALUATION_STATUS_COLOR[props.status])}>
    <EvaluationProgress progression={props.progression} type={constants.EVALUATION_STATUS_COLOR[props.status]} />
    {/*<EvaluationStatus status={props.status} />*/}
  </div>

EvaluationGauge.propTypes = {
  className: T.string,
  status: T.string,
  score: T.number,
  total: T.number,
  progression: T.number,

  size: T.oneOf(['md', 'lg', 'xl'])
}

EvaluationGauge.defaultProps = {
  status: constants.EVALUATION_STATUS_NOT_ATTEMPTED,
  progression: 0
}

export {
  EvaluationGauge
}
