import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {trans} from '#/main/core/translation'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {DataCardHeader} from '#/main/core/data/components/data-card'
import {LineChart} from '#/main/core/layout/chart/line/components/line-chart'

const UserActionCard = props =>
  <div className={classes(`data-card data-card-${props.orientation} data-card-${props.size}`, props.className)}>
    <DataCardHeader
      id={`${props.data.id}`}
      icon={<UserAvatar picture={props.data.doer.picture} alt={true} />}
      flags={[]}
    />
    <div className={'data-card-content'}>
      {React.createElement(`h${props.level}`, {
        key: 'data-card-title',
        className: 'data-card-title'
      }, [
        props.data.doer.name,
        <small key="data-card-subtitle">{`${trans('actions', {}, 'platform')}: ${props.data.actions}`}</small>
      ])}
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
    </div>
  </div>

UserActionCard.propTypes = {
  level: T.number,
  size: T.oneOf(['sm', 'lg']),
  orientation: T.oneOf(['col', 'row']),
  className: T.string,
  data: T.shape({
    id: T.number.isRequired,
    actions: T.number.isRequired,
    doer: T.object.isRequired,
    chartData: T.object.isRequired
  }).isRequired
}

UserActionCard.defaultProps = {
  size: 'sm',
  orientation: 'row',
  level: 2
}

export {
  UserActionCard
}