import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {displayDuration, trans} from '#/main/app/intl'
import {PageContent, PageSection} from '#/main/app/page'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'

import {selectors} from '#/main/evaluation/sequence/store'
import {StatusChart} from '#/main/evaluation/charts/status/components/chart'
import {ProgressionChart} from '#/main/evaluation/charts/progression/components/chart'
import {ScoreChart} from '#/main/evaluation/charts/score/components/chart'

const SequenceDashboardOverview = (props) => {
  const sequence = useSelector(selectors.sequence)
  const totalScore = useSelector(selectors.totalScore)
  const totalActivities = useSelector(selectors.totalActivities)

  return (
    <PageContent>
      <PageSection className="my-4" size="lg">
        <ContentInfoBlocks
          className="mx-auto"
          size="lg"
          items={[
            {
              label: trans('estimated_duration'),
              value: displayDuration(get(sequence, 'estimatedDuration') * 60)
            }, {
              label: trans('activities'),
              value: totalActivities
            }, {
              label: 'Score total',
              value: totalScore ? totalScore : trans('none'),
              help: 'Cette activité n\'est pas notée.'
            }, {
              label: 'Conditions de réussite',
              value: props.score ? props.score : 'Aucune'
            }
          ]}
        />
      </PageSection>

      <PageSection size="full">
        <div className="row">
          <div className="col-3 mb-4">
            <div className="card">
              <div className="card-body p-4">
                <h6 className="page-section-title">Taux de complétion</h6>
              </div>
            </div>
          </div>
          <div className="col-3 mb-4">
            <div className="card">
              <div className="card-body p-4">
                <h6 className="page-section-title">Taux de réussite</h6>
              </div>
            </div>
          </div>

          <div className="col-6 mb-4">
            <StatusChart url={['apiv2_sequence_evaluation_status', {id: sequence.id}]} />
          </div>
        </div>
      </PageSection>

      <PageSection size="full">
        <ProgressionChart />
      </PageSection>

      <PageSection size="full">
        <ScoreChart />
      </PageSection>
    </PageContent>
  )
}

export {
  SequenceDashboardOverview
}
