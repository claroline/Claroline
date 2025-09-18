import React from 'react'
import {Bar, BarChart, CartesianGrid, Label, ReferenceLine, ResponsiveContainer, Tooltip, XAxis, YAxis} from 'recharts'

import {trans} from '#/main/app/intl'

const data = [
  {
    name: '0-10%',
    progression: 4000,
    score: 2400
  }, {
    name: '10-20%',
    progression: 3000,
    score: 1398
  }, {
    name: '20-30%',
    progression: 2000,
    score: 9800
  }, {
    name: '30-40%',
    progression: 2780,
    score: 3908
  }, {
    name: '40-50%',
    progression: 1890,
    score: 4800
  }, {
    name: '50-60%',
    progression: 2390,
    score: 3800
  }, {
    name: '60-70%',
    progression: 3490,
    score: 4300
  }, {
    name: '70-80%',
    progression: 3490,
    score: 4300
  }, {
    name: '80-90%',
    progression: 3490,
    score: 4300
  }, {
    name: '90-100%',
    progression: 3490,
    score: 4300
  }
]

const ScoreChart = () => {
  return (
    <div className="card mb-4">
      <div className="card-body p-4">
        <h6 className="page-section-title mb-4">Score des utilisateurs</h6>

        <div className="row">
          <div className="col-md-2 d-flex flex-column gap-2">
            <div className="rounded-3 text-success-emphasis bg-success-subtle p-3">
              <span className="d-block text-uppercase fs-sm">Maximum</span>
              <b className="fs-4">92</b>
            </div>

            <div className="rounded-3 text-secondary-emphasis bg-secondary-subtle p-3">
              <span className="d-block text-uppercase fs-sm">Moyenne</span>
              <b className="fs-4">50</b>
            </div>

            <div className="rounded-3 text-secondary-emphasis bg-secondary-subtle p-3">
              <span className="d-block text-uppercase fs-sm">Médiane</span>
              <b className="fs-4">50</b>
            </div>

            <div className="rounded-3 text-danger-emphasis bg-danger-subtle p-3">
              <span className="d-block text-uppercase fs-sm">Minimum</span>
              <b className="fs-4">0</b>
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
                  left: 0,
                  bottom: 0
                }}
              >
                <CartesianGrid vertical={false} strokeDasharray="5 5" stroke="var(--bs-secondary)" strokeOpacity={.5} strokeWidth={1} shapeRendering="crispEdges" />

                <XAxis dataKey="name" stroke="var(--bs-secondary)" shapeRendering="crispEdges" strokeOpacity={1} />
                <YAxis stroke="var(--bs-secondary)" shapeRendering="crispEdges" strokeOpacity={1}/>

                <Tooltip />
                {/*<Legend />*/}
                <ReferenceLine x="50-60%" stroke="var(--bs-learning)" strokeWidth={1} shapeRendering="crispEdges">
                  <Label position="top" stroke="var(--bs-learning)">Score de réussite</Label>
                </ReferenceLine>

                <Bar
                  barSize={18}
                  dataKey="score"
                  fill="var(--bs-primary)"
                  radius={[2, 2, 0, 0]}
                  label={trans('score', {}, 'evaluation')}
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
  ScoreChart
}
