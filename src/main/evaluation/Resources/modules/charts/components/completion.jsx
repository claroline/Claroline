import React from 'react'
import {PropTypes as T} from 'prop-types'

import {percent} from '#/main/app/intl/number'
import {CountGauge} from '#/main/core/layout/gauge/components/count-gauge'
import {Html} from '#/main/app/components/html'
import {trans} from '#/main/app/intl'

const CompletionChart = (props) => {
  return (
    <div className="card mb-4">
      <div className="card-body p-4 d-flex flex-column">
        <h6 className="page-section-title mb-4">Taux de complétion</h6>

        <CountGauge
          className="mx-auto"
          value={props.current}
          total={props.total}
          type="primary"
          displayValue={(value) => percent(value, props.total) + '%'}
          width={140}
          height={140}
        />

        <Html className="mt-4 text-center">{trans('users_completion', {count: props.current, total: props.total}, 'evaluation')}</Html>
      </div>
    </div>
  )
}

CompletionChart.propTypes = {
  current: T.number,
  total: T.number
}

export {
  CompletionChart
}
