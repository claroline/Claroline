import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from '#/main/theme/color/utils'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config'
import {toKey} from '#/main/core/scaffolding/text'
import {URL_BUTTON} from '#/main/app/buttons'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'
import {displayDuration, number, trans} from '#/main/app/intl'
import {displayScore} from '#/main/app/data/types/score/utils'
import {DataCard} from '#/main/app/data/components/card'

import {route} from '#/main/core/workspace/routing'
import {constants} from '#/main/evaluation/constants'
import {WorkspaceEvaluation as WorkspaceEvaluationTypes} from '#/main/evaluation/workspace/prop-types'

const WorkspaceCard = (props) =>
  <DataCard
    {...props}
    id={props.data.id}
    primaryAction={!isEmpty(props.primaryAction) ? props.primaryAction : {
      type: URL_BUTTON,
      target: '#'+route(props.data.workspace)
    }}
    className="workspace-evaluation-card"
    poster={props.data.workspace.thumbnail ? asset(props.data.workspace.thumbnail) : null}
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

        {props.data.workspace.name}
      </Fragment>
    }
    subtitle={props.data.workspace.code}
  >
    {-1 === ['xs', 'sm'].indexOf(props.size) && (!props.display || -1 !== props.display.indexOf('footer')) &&
      <div className="workspace-evaluation-details">
        {[
          {
            icon: 'fa fa-fw fa-hourglass-half',
            label: trans('time_spent'),
            value: displayDuration(props.data.duration) || trans('unknown')
          }, {
            icon: 'fa fa-fw fa-award',
            label: trans('score'),
            displayed: !!props.data.scoreMax,
            value: displayScore(props.data.scoreMax, props.data.score)
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

WorkspaceCard.propTypes = {
  size: T.string,
  display: T.array, // from widget list
  data: T.shape(
    WorkspaceEvaluationTypes.propTypes
  ),
  primaryAction: T.shape({
    // action types
  })
}

export {
  WorkspaceCard
}
