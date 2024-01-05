import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from '#/main/theme/color/utils'

import {asset} from '#/main/app/config'
import {toKey} from '#/main/core/scaffolding/text'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'
import {displayDuration, number, trans} from '#/main/app/intl'
import {displayScore} from '#/main/app/data/types/score/utils'
import {DataCard} from '#/main/app/data/components/card'

import {constants} from '#/main/evaluation/constants'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

const ResourceCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    className="resource-evaluation-card"
    poster={props.data.resourceNode.thumbnail ? asset(props.data.resourceNode.thumbnail) : null}
    icon={
      <LiquidGauge
        id={`user-progression-${props.data.id}`}
        type="user"
        value={props.data.progression || 0}
        displayValue={(value) => number(value) + '%'}
        width={'lg' === props.size ? 100 : 60}
        height={'lg' === props.size ? 100 : 60}
      />
    }
    title={
      <Fragment>
        <span className={`badge text-bg-${constants.EVALUATION_STATUS_COLOR[props.data.status]} icon-with-text-right`}>
          {constants.EVALUATION_STATUSES_SHORT[props.data.status]}
        </span>

        {props.data.resourceNode.name}
      </Fragment>
    }
    subtitle={trans(props.data.resourceNode.meta.type, {}, 'resource')}
  >
    {-1 === ['xs', 'sm'].indexOf(props.size) && (!props.display || -1 !== props.display.indexOf('footer')) &&
      <div className="resource-evaluation-details">
        {[
          {
            icon: 'fa fa-fw fa-eye',
            label: trans('views'),
            value: number(props.data.nbOpenings)
          }, {
            icon: 'fa fa-fw fa-redo',
            label: trans('attempts'),
            value: number(props.data.nbAttempts)
          }, {
            icon: 'fa fa-fw fa-hourglass-half',
            label: trans('time_spent'),
            value: displayDuration(props.data.duration) || trans('unknown')
          }, {
            icon: 'fa fa-fw fa-award',
            label: trans('score'),
            displayed: !!props.data.scoreMax,
            value: displayScore(props.data.scoreMax, props.data.score, 100)
          }
        ]
          .filter(item => undefined === item.displayed || item.displayed)
          .map((item, index) => (
            <article key={toKey(item.label)}>
              <span className={item.icon} style={{backgroundColor: schemeCategory20c[(index * 4) + 1]}}/>
              <h5>
                <small>{item.label}</small>
                {item.value}
              </h5>
            </article>
          ))
        }
      </div>
    }
  </DataCard>

ResourceCard.propTypes = {
  size: T.string,
  display: T.array, // from list
  data: T.shape(
    ResourceEvaluationTypes.propTypes
  )
}

export {
  ResourceCard
}
