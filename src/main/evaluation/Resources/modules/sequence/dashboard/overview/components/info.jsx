import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {displayDate, displayDuration, trans, transChoice} from '#/main/app/intl'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'
import {Badge} from '#/main/app/components/badge'

import {selectors} from '#/main/evaluation/sequence/store'

const SequenceDashboardInfo = () => {
  const sequence = useSelector(selectors.sequence)
  const published = useSelector(selectors.published)
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
  } else if (published) {
    status = (
      <Badge className="fs-base" variant="success" subtle={true}>
        {trans('published')}
      </Badge>
    )
  } else {
    status = (
      <Badge className="fs-base" variant="warning" subtle={true}>
        {trans('not_published')}
      </Badge>
    )
  }

  return (
    <ContentInfoBlocks
      className="mx-auto"
      size="lg"
      items={[
        {
          label: trans('estimated_duration'),
          value: get(sequence, 'estimatedDuration') ?
            displayDuration(get(sequence, 'estimatedDuration') * 60) :
            trans('none')
        }, {
          label: 'Score total',
          value: totalScore ? totalScore : trans('none'),
        }, {
          label: trans('success_conditions', {}, 'evaluation'),
          value: transChoice('count_success_conditions', totalSuccessConditions, {count: totalSuccessConditions}, 'evaluation')
        }, {
          label: trans('last_modification'),
          value: displayDate(get(sequence, 'meta.updatedAt'), false, true)
        }, {
          label: trans('status'),
          value: status
        }
      ]}
    />
  )
}

export {
  SequenceDashboardInfo
}
