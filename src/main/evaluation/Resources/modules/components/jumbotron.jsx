import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {constants as baseConstants} from '#/main/evaluation/constants'

import {ContentSizing} from '#/main/app/content/components/sizing'
import {EvaluationGauge} from '#/main/evaluation/components/gauge'
import {displayDate, displayDuration, trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'

const EvaluationJumbotron = (props) =>
  <div className={classes('row', props.className, `bg-${baseConstants.EVALUATION_STATUS_COLOR[get(props.evaluation, 'status')]}-subtle`)}>
    <ContentSizing size="md" className="evaluation-jumbotron my-5">
      <EvaluationGauge
        {...props.evaluation}
      />

      <div className="d-grid gap-3">
        <ContentInfoBlocks
          variant={baseConstants.EVALUATION_STATUS_COLOR[get(props.evaluation, 'status')]}
          size="lg"
          items={[
            {
              icon: 'fa fa-fw fa-eye',
              label: trans('views'),
              value: 100
            }, {
              icon: 'fa fa-fw fa-clock',
              label: trans('duration'),
              value: get(props.evaluation, 'duration') ? displayDuration(get(props.evaluation, 'duration')) : null
            }, {
              icon: 'fa fa-fw fa-history',
              label: trans('last_activity'),
              value: get(props.evaluation, 'date') ? displayDate(props.evaluation.date, false, true) : null
            }
          ]}
        />

        <div className="d-flex gap-2 mt-auto">
          <Button
            type={LINK_BUTTON}
            className={classes('btn flex-fill', `btn-${baseConstants.EVALUATION_STATUS_COLOR[get(props.evaluation, 'status')]}`)}
            label="Continuer"
            target="/continue"
            size="lg"
          />

          <Button
            type={LINK_BUTTON}
            className={classes('btn', `btn-outline-${baseConstants.EVALUATION_STATUS_COLOR[get(props.evaluation, 'status')]}`)}
            label="Télécharger (.pdf)"
            target="/download"
            size="lg"
          />
        </div>
      </div>
    </ContentSizing>
  </div>

EvaluationJumbotron.propTypes = {
  className: T.string,
  evaluation: T.shape({

  })
}

export {
  EvaluationJumbotron
}
