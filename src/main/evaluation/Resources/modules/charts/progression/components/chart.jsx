import React from 'react'
import {Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis} from 'recharts'

import {trans} from '#/main/app/intl'

const data = [
  {
    name: '0-20%',
    progression: 4000,
    score: 2400
  }, {
    name: '20-40%',
    progression: 3000,
    score: 1398
  }, {
    name: '40-60%',
    progression: 2000,
    score: 9800
  }, {
    name: '60-80%',
    progression: 2780,
    score: 3908
  }, {
    name: '80-100%',
    progression: 1890,
    score: 4800
  }
]

const ProgressionChart = () => {
  return (
    <div className="card mb-4">
      <div className="card-body p-4">
        <h6 className="page-section-title mb-4">Progression des utilisateurs</h6>

        <div className="row">
          <div className="col-md-2 d-flex flex-column gap-2">
            <div className="rounded-3 text-success-emphasis bg-success-subtle p-3">
              <span className="d-block text-uppercase fs-sm">Maximum</span>
              <b className="fs-4">100%</b>
            </div>

            <div className="rounded-3 text-secondary-emphasis bg-secondary-subtle p-3">
              <span className="d-block text-uppercase fs-sm">Moyenne</span>
              <b className="fs-4">50%</b>
            </div>

            <div className="rounded-3 text-secondary-emphasis bg-secondary-subtle p-3">
              <span className="d-block text-uppercase fs-sm">Médiane</span>
              <b className="fs-4">50%</b>
            </div>

            <div className="rounded-3 text-danger-emphasis bg-danger-subtle p-3">
              <span className="d-block text-uppercase fs-sm">Minimum</span>
              <b className="fs-4">0%</b>
            </div>
          </div>
          <div className="col-md-10">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart
                width={500}
                height={300}
                data={data}
                margin={{
                  top: 20,
                  right: 0,
                  left: 20,
                  bottom: 0
                }}
              >
                <CartesianGrid vertical={false} strokeDasharray="5 5" stroke="var(--bs-secondary)" strokeOpacity={.5} strokeWidth={1} shapeRendering="crispEdges" />

                <XAxis dataKey="name" stroke="var(--bs-secondary)" shapeRendering="crispEdges" strokeOpacity={1} />
                <YAxis stroke="var(--bs-secondary)" shapeRendering="crispEdges" strokeOpacity={1}/>

                <Tooltip />

                <Bar
                  barSize={50}
                  /*shapeRendering="crispEdges"*/
                  dataKey="progression"
                  fill="var(--bs-primary)"
                  /*activeBar={<Rectangle fill="var(--bs-primary-text-emphasis)" />}*/
                  label={trans('progression', {}, 'evaluation')}
                  radius={[4, 4, 0, 0]}
                />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>
    </div>
  )
}

export {
  ProgressionChart
}
