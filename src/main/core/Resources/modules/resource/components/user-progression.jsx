import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {schemeCategory20c} from 'd3-scale'

import {trans, number, displayDuration} from '#/main/app/intl'
import {toKey} from '#/main/core/scaffolding/text'
import {MenuButton} from '#/main/app/buttons/menu'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'

import {constants as baseConstants} from '#/main/evaluation/constants'
import {constants} from '#/main/core/resource/constants'
import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'

const ProgressionPopover = props =>
  <div className="dropdown-menu">
    <h4 className="dropdown-menu-header">
      <span className={classes('fa', {
        // icons
        'fa-question': baseConstants.EVALUATION_STATUS_UNKNOWN === props.status,
        'fa-eye': baseConstants.EVALUATION_STATUS_OPENED === props.status,
        'fa-sync': baseConstants.EVALUATION_STATUS_INCOMPLETE === props.status,
        'fa-flag-checkered': baseConstants.EVALUATION_STATUS_COMPLETED === props.status,
        'fa-trophy': baseConstants.EVALUATION_STATUS_PASSED === props.status,
        'fa-ban': baseConstants.EVALUATION_STATUS_FAILED === props.status,
        'fa-handshake': baseConstants.EVALUATION_STATUS_PARTICIPATED === props.status,

        // colors
        'ended': -1 !== [baseConstants.EVALUATION_STATUS_PASSED, baseConstants.EVALUATION_STATUS_COMPLETED, baseConstants.EVALUATION_STATUS_PARTICIPATED].indexOf(props.status),
        'failed': baseConstants.EVALUATION_STATUS_FAILED === props.status,
        'in-progress': baseConstants.EVALUATION_STATUS_OPENED === props.status,
        'incomplete': baseConstants.EVALUATION_STATUS_INCOMPLETE === props.status
      })} />

      {constants.EVALUATION_STATUSES[props.status]}
    </h4>

    <ul className="list-group list-group-striped">
      {props.items
        .filter(item => undefined === item.displayed || item.displayed)
        .map((item, index) => (
          <li key={toKey(item.label)} className="list-group-item">
            <span className={item.icon} style={{backgroundColor: schemeCategory20c[(index * 4) + 1]}} />
            <h5 className="h4">
              <small>{item.label}</small>
              {item.value}
            </h5>
          </li>
        ))
      }
    </ul>
  </div>

ProgressionPopover.propTypes = {
  status: T.string,
  items: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    label: T.string.isRequired,
    value: T.oneOfType([T.string, T.number]),
    displayed: T.bool
  }))
}

ProgressionPopover.defaultProps = {
  status: baseConstants.EVALUATION_STATUS_UNKNOWN
}

/**
 * Renders a gauge to display progression of the user in the resource evaluation.
 */
const UserProgression = props => {
  let progression = 0
  if (props.userEvaluation.progression) {
    progression = props.userEvaluation.progression
    if (props.userEvaluation.progressionMax) {
      progression = (progression / props.userEvaluation.progressionMax) * 100
    }
  }

  return (
    <MenuButton
      id="resource-progression"
      containerClassName="resource-user-progression"
      menu={(
        <ProgressionPopover
          status={props.userEvaluation.status}
          items={[
            {
              icon: 'fa fa-fw fa-award',
              label: trans('score'),
              displayed: !!props.userEvaluation.scoreMax,
              value: (number(props.userEvaluation.score) || 0) + ' / ' + number(props.userEvaluation.scoreMax)
            }, {
              icon: 'fa fa-fw fa-percent',
              label: 'Complétion',
              value: number(progression) + '%'
            }, {
              icon: 'fa fa-fw fa-eye',
              label: trans('views'),
              value: number(props.userEvaluation.nbOpenings)
            }, {
              icon: 'fa fa-fw fa-redo',
              label: trans('attempts'),
              value: number(props.userEvaluation.nbAttempts)
            }, {
              icon: 'fa fa-fw fa-hourglass-half',
              label: trans('time_spent'),
              value: displayDuration(props.userEvaluation.duration) || trans('unknown')
            }
          ]}
        />
      )}
    >
      <LiquidGauge
        id="user-progression"
        type="user"
        value={progression}
        displayValue={(value) => number(value) + '%'}
        width={props.width}
        height={props.height}
      />
    </MenuButton>
  )
}

UserProgression.propTypes = {
  userEvaluation: T.shape(
    UserEvaluationTypes.propTypes
  ).isRequired,
  width: T.number,
  height: T.number
}

export {
  UserProgression
}
