import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import {Bar, BarChart, CartesianGrid, ResponsiveContainer, XAxis, YAxis} from 'recharts'

import {trans} from '#/main/app/intl'
import {useFetch} from '#/main/app/api/fetch'
import {percent} from '#/main/app/intl/number'

const ProgressionChartMetrics = ({min = 0, max = 0, avg = 0}) => {
  return (
    <div className="col-md-2 d-flex flex-column gap-2">
      <div className="rounded-3 text-success-emphasis bg-success-subtle p-3">
        <span className="d-block text-uppercase fs-sm">Maximum</span>
        <b className="fs-4">{percent(max, 100)}%</b>
      </div>

      <div className="rounded-3 text-secondary-emphasis bg-secondary-subtle p-3">
        <span className="d-block text-uppercase fs-sm">Moyenne</span>
        <b className="fs-4">{percent(avg, 100)}%</b>
      </div>

      {/*<div className="rounded-3 text-secondary-emphasis bg-secondary-subtle p-3">
        <span className="d-block text-uppercase fs-sm">Médiane</span>
        <b className="fs-4">50</b>
      </div>*/}

      <div className="rounded-3 text-danger-emphasis bg-danger-subtle p-3">
        <span className="d-block text-uppercase fs-sm">Minimum</span>
        <b className="fs-4">{percent(min, 100)}%</b>
      </div>
    </div>
  )
}

ProgressionChartMetrics.propTypes = {
  min: T.number,
  max: T.number,
  avg: T.number
}

const ProgressionChart = ({name, url, className}) => {
  const [progressionData] = useFetch(name, url)

  const totalUsers = get(progressionData, 'progression', []).reduce((acc, current) => {
    return acc + current.users
  }, 0)

  return (
    <div role="presentation" className={className}>
      <h3 className="h5 mb-4">
        {trans('users_progression', {}, 'evaluation')}
      </h3>

      <div className="row">
        <ProgressionChartMetrics
          min={get(progressionData, 'stats.min')}
          max={get(progressionData, 'stats.max')}
          avg={get(progressionData, 'stats.avg')}
        />

        <div className="col-md-10">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart
              width={500}
              height={300}
              data={get(progressionData, 'progression', []).map((e) => ({
                value: e.value - 20,
                users: e.users
              }))}
              margin={{
                top: 20,
                right: 0,
                left: 20,
                bottom: 0
              }}
            >
              <CartesianGrid vertical={false} strokeDasharray="5 5" stroke="var(--bs-secondary)" strokeOpacity={.5} strokeWidth={1} shapeRendering="crispEdges" />

              <XAxis
                hide={false}
                dataKey="value"
                includeHidden={true}
                name={trans('progression', {}, 'evaluation')}
                stroke="var(--bs-secondary)"
                shapeRendering="crispEdges"
                strokeOpacity={1}
                unit="%"
                domain={[0, 100]}
                scale="band"
              />
              <YAxis domain={[0, totalUsers]} includeHidden={true} dataKey="users" name={trans('user')} stroke="var(--bs-secondary)" shapeRendering="crispEdges" strokeOpacity={1}/>

              <Bar
                barSize={50}
                label={{ position: 'top' }}
                dataKey="users"
                fill="var(--bs-primary)"
                radius={[4, 4, 0, 0]}
              />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  )
}

ProgressionChart.propTypes = {
  className: T.string,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired
}

export {
  ProgressionChart
}
