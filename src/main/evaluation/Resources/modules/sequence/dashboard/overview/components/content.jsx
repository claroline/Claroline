import React, {useState} from 'react'
import {useSelector} from 'react-redux'
import classes from 'classnames'

import {PieChart, Pie, Sector, ResponsiveContainer, Cell} from '#/main/app/charts'
import {constants} from '#/main/app/constants'
import {DotColor} from '#/main/app/components/dot'
import {percent} from '#/main/app/intl/number'
import {trans} from '#/main/app/intl'

import {selectors} from '#/main/evaluation/sequence/dashboard/store'

const renderActiveShape = (props) => {
  const { cx, cy, innerRadius, outerRadius, startAngle, endAngle, payload, value, fill } = props

  return (
    <g>
      <text x="50%" y={110} textAnchor="middle">
        <tspan className="fw-semibold fs-1" fill="var(--bs-body-color)">
          {value}
        </tspan>

        <tspan className="fw-bolder fs-lg"  fill="var(--bs-secondary-color)" textAnchor="middle" x="50%" dy="30px">
          {payload.label}
        </tspan>
      </text>

      <Sector
        cx={cx}
        cy={cy}
        innerRadius={innerRadius}
        outerRadius={outerRadius}
        startAngle={startAngle}
        endAngle={endAngle}
        fill={fill}
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
        fill={fill}
      />
    </g>
  )
}

const SequenceDashboardContent = () => {
  const data = useSelector(selectors.activities)

  let total = 0
  if (data) {
    data.map((status) => {
      total = total + status.value
    })
  }

  const [activeIndex, setActiveIndex] = useState(null)

  const chartData = []
    .concat(data || [])

  return (
    <div className="d-flex flex-column" role="presentation">
      <div className="d-flex flex-row align-items-center gap-3 mb-4">
        <h3 className="h5 mb-0 me-auto">
          {trans('content')}
        </h3>
      </div>

      <ResponsiveContainer height={220} width="100%" className="mx-auto">
        <PieChart>
          <Pie
            activeIndex={activeIndex}
            activeShape={renderActiveShape}
            data={chartData}
            innerRadius={85}
            outerRadius={100}
            paddingAngle={2}
            dataKey="value"
            onClick={(_, index) => setActiveIndex(index)}
            onMouseEnter={(_, index) => setActiveIndex(index)}
            onMouseLeave={() => setActiveIndex(null)}
          >
            {chartData.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={constants.COLORS[index]} strokeWidth={0} />
            ))}
          </Pie>

          {null === activeIndex &&
            <text x="50%" y={110} textAnchor="middle">
              <tspan className="fw-semibold fs-1" fill="var(--bs-body-color)">
                {total}
              </tspan>

              <tspan className="fw-bolder fs-lg"  fill="var(--bs-secondary-color)" textAnchor="middle" x="50%" dy="30px">
                {trans('activities')}
              </tspan>
            </text>
          }
        </PieChart>
      </ResponsiveContainer>

      {data &&
        <ul className="list-unstyled mb-0 flex-fill mt-4 border rounded-2">
          {data.map((d, i) => (
            <li key={d.label} className={classes('py-2 px-3 d-flex flex-row align-items-center gap-3 flex-wrap', {
              'border-top': i !== 0
            })}>
              <span className="d-flex gap-2 align-items-center text-nowrap">
                <DotColor color={constants.COLORS[i]} />
                {d.label}
              </span>

              <b className="text-body-secondary ms-auto text-nowrap">
                {d.value} ({percent(d.value, total)+'%'})
              </b>
            </li>
          ))}
        </ul>
      }
    </div>
  )
}

export {
  SequenceDashboardContent
}
