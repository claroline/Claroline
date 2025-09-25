import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans, displayDuration} from '#/main/app/intl'
import {Toolbar, ActionTypes, PromisedActionTypes, pickActionSet, constants as actionConst} from '#/main/app/action'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {DescriptionList} from '#/main/app/data/components/description-list'

import {constants} from '#/main/evaluation/constants'
import {EvaluationGauge} from '#/main/evaluation/components/gauge'
import {UserEvaluation as UserEvaluationTypes} from '#/main/evaluation/prop-types'

const UserProgressionDetails = (props) => {
  return (
    <div
      className={classes('modal-aside bg-body-tertiary p-4 rounded-start-3 d-flex flex-column flex-shrink-0 w-100',
        `text-${constants.EVALUATION_STATUS_COLOR[get(props.evaluation, 'status')]}-emphasis`,
        `bg-${constants.EVALUATION_STATUS_COLOR[get(props.evaluation, 'status')]}-subtle`
      )}
      style={{maxWidth: '16rem'}}
    >
      {props.evaluation.certified &&
        <TooltipOverlay id="certified" tip={trans('certified_help', {}, 'evaluation')} position="bottom">
          <div className="me-auto fw-bolder mb-3 mt-n2 fs-sm ms-n2 cursor-help">
            <span className="fa fa-certificate me-2" aria-hidden={true} />
            {trans('certified', {}, 'evaluation')}
          </div>
        </TooltipOverlay>
      }

      <EvaluationGauge
        className="mx-auto"
        status={constants.EVALUATION_STATUS_NOT_ATTEMPTED}
        size="lg"
        {...props.evaluation}
      />

      {props.actions &&
        <Toolbar
          className="mx-auto"
          buttonName={classes('btn btn-text-body text-reset', `focus-ring-${constants.EVALUATION_STATUS_COLOR[get(props.evaluation, 'status')]}`)}
          tooltip="bottom"
          actions={pickActionSet(actionConst.ACTION_SET_DETAILS, props.actions)}
        />
      }

      <div className="d-flex flex-wrap gap-2 justify-content-center mt-3">
        {[
          {
            icon: 'fa far fa-clock',
            value: get(props.evaluation, 'duration') ? displayDuration(get(props.evaluation, 'duration')) : null,
            label: trans('duration'),
            displayed: !!get(props.evaluation, 'duration')
          }, {
            icon: 'fa far fa-clock',
            label: trans('estimated_duration'),
            value: get(props.evaluation, 'estimatedDuration') ? displayDuration(get(props.evaluation, 'estimatedDuration')) : '-',
            displayed: !get(props.evaluation, 'duration')
          }
        ].concat(props.additional || [])
          .filter(info => undefined === info.displayed || info.displayed)
          .map(info =>
            <TooltipOverlay tip={info.label} key={info.label} position="bottom">
              <div className="bg-body rounded-2 px-2 py-1 d-flex flex-row flex-nowrap align-items-center gap-2">
                <span className={info.icon} aria-hidden={true} />
                {info.value}
              </div>
            </TooltipOverlay>
          )
        }
      </div>

      <DescriptionList
        className="border-top-0 border-bottom-0 mb-0 mt-2"
        data={props.evaluation}
        variant={constants.EVALUATION_STATUS_COLOR[get(props.evaluation, 'status')]}
        fields={[
          {
            name: 'lastActivityAt',
            type: 'date',
            label: trans('last_activity_at'),
            options: {long: true, time: true},
            placeholder: '-'
          }, {
            name: 'startedAt',
            type: 'date',
            label: trans('started_at'),
            options: {long: true, time: true},
            placeholder: '-'
          }, {
            name: 'endedAt',
            type: 'date',
            label: trans('ended_at'),
            options: {long: true, time: true},
            placeholder: '-'
          }
        ]}
      />
    </div>
  )
}

UserProgressionDetails.propTypes = {
  evaluation: T.shape(
    UserEvaluationTypes.propTypes
  ).isRequired,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),
  additional: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    label: T.string.isRequired,
    value: T.any.isRequired
  }))
}

export {
  UserProgressionDetails
}
