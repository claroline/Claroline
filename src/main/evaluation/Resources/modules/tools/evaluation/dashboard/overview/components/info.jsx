import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {displayDate, trans, transChoice} from '#/main/app/intl'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'
import {Badge} from '#/main/app/components/badge'

import {selectors} from '#/main/evaluation/tools/evaluation/dashboard/store'

const EvaluationDashboardInfo = () => {
  const workspace = useSelector(selectors.workspace)
  const archived = useSelector(selectors.archived)
  const totalScore = useSelector(selectors.totalScore)
  const totalSuccessConditions = useSelector(selectors.countSuccessCondition)

  let status
  if (archived) {
    status = (
      <Badge className="fs-base" variant="danger" subtle={true}>
        {trans('archived')}
      </Badge>
    )
  } else {
    status = (
      <Badge className="fs-base" variant="success" subtle={true}>
        {trans('published')}
      </Badge>
    )
  }

  return (
    <ContentInfoBlocks
      className="mx-auto"
      size="lg"
      items={[
        {
          label: 'Score total',
          value: totalScore ? totalScore : trans('none')
        }, {
          label: trans('success_conditions', {}, 'evaluation'),
          value: transChoice('count_success_conditions', totalSuccessConditions, {count: totalSuccessConditions}, 'evaluation')
        }, {
          label: trans('last_modification'),
          value: displayDate(get(workspace, 'meta.updated'), false, true)
        }, {
          label: trans('status'),
          value: status
        }
      ]}
    />
  )
}

export {
  EvaluationDashboardInfo
}
