import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {PieChart, Pie, Sector, ResponsiveContainer, Cell} from '#/main/app/charts'
import {constants} from '#/main/evaluation/constants'
import {Dot} from '#/main/app/components/dot'
import {percent} from '#/main/app/intl/number'
import {useFetch} from '#/main/app/api/fetch'
import {transChoice} from '#/main/app/intl'

const renderActiveShape = (props) => {
  const { cx, cy, innerRadius, outerRadius, startAngle, endAngle, payload, value } = props

  const fill = constants.EVALUATION_STATUS_COLOR[payload.status]

  return (
    <g>
      <text className="text-uppercase fw-bold fs-5" textAnchor="middle" fill={`var(--bs-${fill})`} x={cx} y={cy} dy={30}>
        {constants.EVALUATION_STATUSES_SHORT[payload.status]}
      </text>
      <text textAnchor="middle" fill="var(--bs-secondary-color)" x={cx} y={cy} dy={50}>
        {transChoice('count_users', value, {count: value})}
      </text>

      <Sector
        cx={cx}
        cy={cy}
        innerRadius={innerRadius}
        outerRadius={outerRadius}
        startAngle={startAngle}
        endAngle={endAngle}
        fill={`var(--bs-${fill})`}
        strokeWidth={0}
      />
      <Sector
        cx={cx}
        cy={cy}
        startAngle={startAngle}
        endAngle={endAngle}
        innerRadius={outerRadius + 6}
        outerRadius={outerRadius + 10}
        strokeWidth={0}
        fill={`var(--bs-${fill})`}
      />
    </g>
  )
}

const StatusChart = (props) => {
  const [data] = useFetch('evaluationStatusChart', props.url)

  let max = 0
  let maxValue = 0
  let total = 0
  if (data) {
    data.map((status, index) => {
      total = total + status.value
      if (status.value > maxValue) {
        maxValue = status.value
        max = index
      }
    })
  }

  const [activeIndex, setActiveIndex] = useState(max)

  const chartData = []
    .concat(data || [])
    .sort((a, b) => constants.EVALUATION_STATUS_PRIORITY[a.status] - constants.EVALUATION_STATUS_PRIORITY[b.status])

  return (
    <div className="card" role="presentation">
      <div className="card-body p-4 d-flex flex-row align-items-center justify-content-center gap-4">
        <ResponsiveContainer height={260} width={360} className="mb-n5">
          <PieChart>
            <Pie
              activeIndex={activeIndex}
              activeShape={renderActiveShape}
              data={chartData}
              startAngle={180}
              endAngle={0}
              innerRadius={90}
              outerRadius={120}
              paddingAngle={2}
              dataKey="value"
              onClick={(_, index) => setActiveIndex(index)}
              onMouseEnter={(_, index) => setActiveIndex(index)}
            >
              {chartData.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={`var(--bs-${constants.EVALUATION_STATUS_COLOR[entry.status]})`} strokeWidth={0} />
              ))}
            </Pie>
          </PieChart>
        </ResponsiveContainer>

        {data &&
          <ul className="list-unstyled mb-0 fs-sm flex-fill">
            {data.map((d, i) => (
              <li key={d.status} className={classes('py-2 d-flex flex-row align-items-center gap-3', {
                'border-top': i !== 0
              })}>
                <span className="d-flex gap-2 align-items-center">
                  <Dot variant={constants.EVALUATION_STATUS_COLOR[d.status]} />
                  {constants.EVALUATION_STATUSES_SHORT[d.status]}
                </span>

                <b className="text-body-secondary ms-auto">
                  {percent(d.value, total)+'%'}
                </b>
              </li>
            ))}
          </ul>
        }
      </div>
    </div>
  )
}

StatusChart.propTypes = {
  url: T.oneOfType([T.string, T.array]).isRequired,
  data: T.arrayOf(T.shape({
    name: T.string.isRequired,
    value: T.number
  }))
}

export {
  StatusChart
}
