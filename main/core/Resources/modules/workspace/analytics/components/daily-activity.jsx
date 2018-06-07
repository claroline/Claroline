import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {Heading} from '#/main/core/layout/components/heading'
import {LineChart} from '#/main/core/layout/chart/line/components/line-chart'

const DailyActivity = props =>
  <section>
    <Heading level={2}>
      {trans('last_30_days_activity')}
    </Heading>

    <LineChart
      responsive={true}
      data={props.activity}
      xAxisLabel={{
        show: true,
        text: trans('date'),
        grid: true
      }}
      yAxisLabel={{
        show: true,
        text: trans('actions'),
        grid: true
      }}
      height={250}
      width={1200}
      showArea={true}
      margin={{
        left: 50,
        top: 5,
        right: 1,
        bottom: 50
      }}
    />
  </section>

DailyActivity.propTypes = {
  activity: T.object.isRequired // todo check
}

export {
  DailyActivity
}