import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LogList} from '#/main/core/layout/logs'

import {selectors as dashboardSelectors} from '#/main/core/resource/dashboard/store'

const Logs = (props) =>
  <LogList
    id={props.resourceId}
    name={dashboardSelectors.STORE_NAME + '.logs'}
    listUrl={['apiv2_resource_logs_list', {resourceId: props.resourceId}]}
    // actions={props.actions}
    chart={props.chart}
    getChartData={props.getChartData}
    queryString={props.queryString}
  />

Logs.propTypes = {
  resourceId: T.number.isRequired,
  // actions: T.array.isRequired,
  chart: T.object.isRequired,
  getChartData: T.func.isRequired,
  queryString: T.string
}

export {
  Logs
}