import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl'
import {percent, precision} from '#/main/app/intl/number'
import {useFetch} from '#/main/app/api/fetch'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors} from '#/main/core/resource/dashboard/store'
import {MetricsChart} from '#/main/evaluation/chart/components/metrics'

const ResourceDashboardMetrics = () => {
  const path = useSelector(selectors.path)

  const resource = useSelector(resourceSelectors.resourceNode)
  const hasEvaluation = useSelector(resourceSelectors.hasEvaluation)
  const hasSuccessCondition = useSelector(resourceSelectors.hasSuccessCondition)

  const [metricsData] = useFetch(selectors.STORE_NAME+'.metrics', ['apiv2_resource_metrics', {id: resource.id}])

  const views = get(metricsData, 'views', {})
  const completion = get(metricsData, 'completion', {})
  const success = get(metricsData, 'success', {})

  let participants = completion.total || 0
  let avgView = 0
  if (participants) {
    avgView = precision(views.count / participants, 0)
  }

  const metrics = [
    {
      icon: 'eye',
      title: trans('views'),
      primaryValue: views.count || 0,
      secondaryValue: transChoice('count_users', views.visitors || 0, {count: views.visitors || 0})
    }
  ]

  if (hasEvaluation) {
    metrics.push({
      icon: 'user',
      title: trans('participants'),
      primaryValue: participants,
      secondaryValue: transChoice('avg_participant_view', avgView, {count: avgView}, 'evaluation')
    })
    metrics.push({
      icon: 'flag-checkered',
      title: trans('completion_rate', {}, 'evaluation'),
      primaryValue: percent(completion.count, completion.total)+'%',
      secondaryValue: transChoice('users_total', completion.count || 0, {count: completion.count || 0, total: completion.total || 0}, 'evaluation'),
      moreLink: path+'/stats'
    })
  }

  if (hasSuccessCondition) {
    metrics.push({
      icon: 'circle-check',
      title: trans('success_rate', {}, 'evaluation'),
      primaryValue: percent(success.count, success.total)+'%',
      secondaryValue: transChoice('users_total', success.count || 0, {count: success.count || 0, total: success.total || 0}, 'evaluation'),
      moreLink: path+'/stats'
    })
  }

  return (
    <MetricsChart
      data={metrics}
      loaded={!isEmpty(metricsData)}
    />
  )
}

export {
  ResourceDashboardMetrics
}
