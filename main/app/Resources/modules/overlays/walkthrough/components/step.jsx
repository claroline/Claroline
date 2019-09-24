import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {Popover} from '#/main/app/overlays/popover/components/popover'
import {ProgressBar} from '#/main/core/layout/components/progress-bar'

// todo : manage icon components

const WalkThroughStep = props =>
  <Popover
    id={toKey(props.title || props.message)}
    className={classes('walkthrough-popover', props.className)}
    tabIndex={-1}
    placement={props.placement}
    positionLeft={props.positionLeft}
    positionTop={props.positionTop}
  >
    <ProgressBar value={props.progression} size="xs" />

    {props.icon &&
      <span className={classes('walkthrough-icon', props.icon)} />
    }

    <div className="walkthrough-content">
      {props.title &&
        <h3 className="walkthrough-title">{props.title}</h3>
      }

      {props.message}

      {props.link &&
        <a className="walkthrough-link" href={props.link}>
          <span className="fa fa-question-circle icon-with-text-right" />
          {trans('learn-more', {}, 'actions')}
        </a>
      }
    </div>

    {props.info &&
      <div className="walkthrough-info">
        {props.info}
      </div>
    }

    {props.requiredInteraction &&
      <div className="walkthrough-interaction">
        <span className="fa fa-hand-pointer icon-with-text-right" />
        {props.requiredInteraction.message}
      </div>
    }

    <div className="walkthrough-nav">
      <CallbackButton
        className="btn-link btn-skip"
        callback={props.skip}
        primary={true}
        size="sm"
      >
        {trans('skip', {}, 'actions')}
      </CallbackButton>

      <CallbackButton
        className="btn-link btn-previous"
        callback={props.previous}
        disabled={!props.hasPrevious}
        size="sm"
      >
        <span className="fa fa-angle-double-left" />
        <span className="sr-only">{trans('previous')}</span>
      </CallbackButton>

      <CallbackButton
        className="btn btn-next"
        callback={props.next}
        disabled={!isEmpty(props.requiredInteraction)}
        primary={true}
        size="sm"
      >
        {isEmpty(props.requiredInteraction) ? trans('next') : trans('action_required', {}, 'walkthrough')}

        {isEmpty(props.requiredInteraction) &&
          <span className="fa fa-angle-double-right icon-with-text-left"/>
        }
      </CallbackButton>
    </div>
  </Popover>

WalkThroughStep.propTypes = {
  className: T.string,
  progression: T.number,

  // position
  placement: T.oneOf(['left', 'top', 'right', 'bottom']),
  positionLeft: T.number,
  positionTop: T.number,

  // content
  icon: T.oneOfType([T.string, T.element]),
  title: T.string,
  message: T.string.isRequired,
  info: T.string,
  link: T.string,

  // interaction
  requiredInteraction: T.shape({
    type: T.oneOf(['click']),
    target: T.string.isRequired,
    message: T.string.isRequired
  }),

  // navigation
  hasPrevious: T.bool.isRequired,
  skip: T.func.isRequired,
  previous: T.func.isRequired,
  next: T.func.isRequired
}

WalkThroughStep.defaultProps = {
  progression: 0,
  placement: 'bottom'
}

export {
  WalkThroughStep
}
