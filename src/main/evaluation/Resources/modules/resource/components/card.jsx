import React from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'

import {asset} from '#/main/app/config'
import {toKey} from '#/main/core/scaffolding/text'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'
import {displayDuration, number, trans} from '#/main/app/intl'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DataCard} from '#/main/app/data/components/card'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {MODAL_RESOURCE_EVALUATIONS} from '#/main/evaluation/modals/resource-evaluations'
import {ResourceUserEvaluation as ResourceUserEvaluationTypes} from '#/main/evaluation/resource/prop-types'

const ResourceCard = (props) => {
  let progression = 0
  if (props.data.progression) {
    progression = props.data.progression
    if (props.data.progressionMax) {
      progression = (progression / props.data.progressionMax) * 100
    }
  }

  return (
    <DataCard
      {...props}
      id={props.data.id}
      className="resource-evaluation-card"
      poster={props.data.resourceNode.thumbnail ? asset(props.data.resourceNode.thumbnail.url) : null}
      icon={
        <LiquidGauge
          id={`user-progression-${props.data.id}`}
          type="user"
          value={progression}
          displayValue={(value) => number(value) + '%'}
          width={60}
          height={60}
        />
      }
      title={props.data.resourceNode.name}
      subtitle={trans(props.data.resourceNode.meta.type, {}, 'resource')}
      actions={[
        {
          name: 'open',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-external-link',
          label: trans('open', {}, 'actions'),
          target: resourceRoute(props.data.resourceNode)
        }, {
          name: 'about',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-info',
          label: trans('show-info', {}, 'actions'),
          modal: [MODAL_RESOURCE_EVALUATIONS, {
            userEvaluation: props.data
          }]
        }
      ]}
    >
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
            label: 'Temps passé',
            value: displayDuration(props.data.duration) || trans('unknown')
          }, {
            icon: 'fa fa-fw fa-award',
            label: trans('score'),
            displayed: !!props.data.scoreMax,
            value: (number(props.data.score) || 0) + ' / ' + number(props.data.scoreMax)
          }
        ]
          .filter(item => undefined === item.displayed || item.displayed)
          .map((item, index) => (
            <article key={toKey(item.label)}>
              <span className={item.icon} style={{backgroundColor: schemeCategory20c[(index * 4) + 1]}} />
              <h5>
                <small>{item.label}</small>
                {item.value}
              </h5>
            </article>
          ))
        }
      </div>
    </DataCard>
  )
}

ResourceCard.propTypes = {
  data: T.shape(
    ResourceUserEvaluationTypes.propTypes
  )
}

export {
  ResourceCard
}
