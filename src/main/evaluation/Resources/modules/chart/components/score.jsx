import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import {
  CartesianGrid, Cell,
  Label,
  ReferenceLine,
  ResponsiveContainer, Scatter,
  ScatterChart,
  Tooltip,
  XAxis,
  YAxis
} from 'recharts'

import {number, trans} from '#/main/app/intl'
import {useFetch} from '#/main/app/api/fetch'

const ScoreChartMetrics = ({totalScore, min = 0, max = 0, avg = 0}) => {
  return (
    <div className="col-md-2 d-flex flex-column gap-2">
      <div className="rounded-3 text-success-emphasis bg-success-subtle p-3">
        <span className="d-block text-uppercase fs-sm">Maximum</span>
        <b className="fs-4">{number(max * totalScore)}</b>
      </div>

      <div className="rounded-3 text-secondary-emphasis bg-secondary-subtle p-3">
        <span className="d-block text-uppercase fs-sm">Moyenne</span>
        <b className="fs-4">{number(avg * totalScore)}</b>
      </div>

      {/*<div className="rounded-3 text-secondary-emphasis bg-secondary-subtle p-3">
        <span className="d-block text-uppercase fs-sm">Médiane</span>
        <b className="fs-4">50</b>
      </div>*/}

      <div className="rounded-3 text-danger-emphasis bg-danger-subtle p-3">
        <span className="d-block text-uppercase fs-sm">Minimum</span>
        <b className="fs-4">{number(min * totalScore)}</b>
      </div>
    </div>
  )
}

ScoreChartMetrics.propTypes = {
  totalScore: T.number.isRequired,
  min: T.number,
  max: T.number,
  avg: T.number
}

const ScoreChart = ({className, name, url, totalScore, successScore = null}) => {
  const [scoreData] = useFetch(name, url)

  const chartData = get(scoreData, 'scores', []).map(s => ({
    user: s.user,
    score: number(s.score * totalScore)
  }))

  return (
    <div className={className}>
      <h3 className="h5 mb-4">
        {trans('users_score', {}, 'evaluation')}
      </h3>

      <div className="row">
        <ScoreChartMetrics
          min={get(scoreData, 'stats.min')}
          max={get(scoreData, 'stats.max')}
          avg={get(scoreData, 'stats.avg')}
          totalScore={totalScore}
        />

        <div className="col-md-10">
          <ResponsiveContainer width="100%" height="100%">
            <ScatterChart
              width={500}
              height={300}
              margin={{
                top: 20,
                right: 0,
                left: 0,
                bottom: 0
              }}
            >
              <CartesianGrid vertical={false} strokeDasharray="5 5" stroke="var(--bs-secondary)" strokeOpacity={.5} strokeWidth={1} shapeRendering="crispEdges" />

              <XAxis type="category" dataKey="user" name={trans('user')} stroke="var(--bs-secondary)" shapeRendering="crispEdges" strokeOpacity={1} hide={true} allowDuplicatedCategory={true} />
              <YAxis domain={[0, totalScore]} type="number" dataKey="score" name={trans('score', {}, 'evaluation')} stroke="var(--bs-secondary)" shapeRendering="crispEdges" strokeOpacity={1}/>

              <Tooltip />
              {successScore &&
                <ReferenceLine y={successScore} stroke="var(--bs-primary)" strokeWidth={1} shapeRendering="crispEdges">
                  <Label position="top" stroke="var(--bs-primary)">{trans('success_score', {}, 'evaluation')}</Label>
                </ReferenceLine>
              }

              <Scatter data={chartData}>
                {chartData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={classes({
                    'var(--bs-success)': successScore && entry.score >= successScore,
                    'var(--bs-danger)': successScore && entry.score < successScore,
                    'var(--bs-primary)': !successScore
                  })} />
                ))}
              </Scatter>
            </ScatterChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  )
}

ScoreChart.propTypes = {
  className: T.string,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  totalScore: T.number.isRequired,
  successScore: T.number
}

export {
  ScoreChart
}
