import React, {useState} from 'react'
import {PieChart, Pie, Sector, ResponsiveContainer, Cell} from 'recharts'

import {constants} from '#/main/evaluation/constants'

const data = [
  { name: constants.EVALUATION_STATUS_NOT_ATTEMPTED, value: 200 },
  { name: constants.EVALUATION_STATUS_INCOMPLETE, value: 400 },
  //{ name: constants.EVALUATION_STATUS_COMPLETED, value: 200 },
  { name: constants.EVALUATION_STATUS_PENDING, value: 200 },
  { name: constants.EVALUATION_STATUS_FAILED, value: 100 },
  { name: constants.EVALUATION_STATUS_PASSED, value: 200 }
];

const renderActiveShape = (props) => {
  const { cx, cy, innerRadius, outerRadius, startAngle, endAngle, payload, value } = props;

  const fill = constants.EVALUATION_STATUS_COLOR[payload.name]

  return (
    <g>
      <text className="text-uppercase fw-bold fs-5" textAnchor="middle" fill={`var(--bs-${fill})`} x={cx} y={cy} dy={30}>{constants.EVALUATION_STATUSES_SHORT[payload.name]}</text>
      <text textAnchor="middle" fill={`var(--bs-secondary-color)`} x={cx} y={cy} dy={50}>{value} utilisateurs</text>

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
  );
};

const StatusChart = (props) => {
  let max = 0
  let maxValue = 0
  data.map((status, index) => {
    if (status.value > maxValue) {
      maxValue = status.value
      max = index
    }
  })

  const [activeIndex, setActiveIndex] = useState(max)

  return (
    <ResponsiveContainer height={260} width={260}>
      <PieChart>
        <Pie
          activeIndex={activeIndex}
          activeShape={renderActiveShape}
          data={data}
          startAngle={180}
          endAngle={0}
          innerRadius={90}
          outerRadius={120}
          paddingAngle={2}
          dataKey="value"
          onClick={(_, index) => setActiveIndex(index)}
          onMouseEnter={(_, index) => setActiveIndex(index)}
        >
          {data.map((entry, index) => (
            <Cell key={`cell-${index}`} fill={`var(--bs-${constants.EVALUATION_STATUS_COLOR[entry.name]})`} strokeWidth={0} />
          ))}
        </Pie>
      </PieChart>
    </ResponsiveContainer>
  )
}

export {
  StatusChart
}
