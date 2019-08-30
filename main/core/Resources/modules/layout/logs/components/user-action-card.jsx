import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/content/card/components/data'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {LineChart} from '#/main/core/layout/chart/line/components/line-chart'

const UserActionCard = props =>
  <DataCard
    {...props}
    icon={
      <UserAvatar
        picture={get(props.data, 'doer.picture') ? {url: get(props.data, 'doer.picture')} : undefined}
        alt={true}
      />
    }
    title={props.data.doer.name}
    subtitle={`${trans('actions', {}, 'platform')}: ${props.data.actions}`}
  >
    <div className="text-center">
      <LineChart
        style={{maxHeight: 100}}
        data={props.data.chartData}
        xAxisLabel={{
          show: false,
          text: trans('date'),
          grid: true
        }}
        yAxisLabel={{
          show: false,
          text: trans('actions'),
          grid: true
        }}
        responsive={true}
        height={100}
        width={600}
        showArea={false}
        margin={{
          top: 0,
          bottom: 50,
          left: 50,
          right: 20
        }}
      />
    </div>
  </DataCard>

UserActionCard.propTypes = {
  data: T.shape({
    id: T.number.isRequired,
    actions: T.number.isRequired,
    doer: T.object.isRequired,
    chartData: T.object.isRequired
  }).isRequired
}

export {
  UserActionCard
}