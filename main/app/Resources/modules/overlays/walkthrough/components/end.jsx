import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {Popover} from '#/main/app/overlays/popover/components/popover'

import {Walkthrough as WalkthroughTypes} from '#/main/app/overlays/walkthrough/prop-types'

// todo : manage icon components

const WalkThroughEnd = props =>
  <Popover
    id="walkthrough-end-step"
    className="walkthrough-popover walkthrough-popover-centered"
    tabIndex={-1}
  >
    {props.icon &&
      <span className={classes('walkthrough-icon', props.icon)} />
    }

    <div className="walkthrough-content">
      <h3 className="walkthrough-title">{props.title}</h3>

      {props.message}

      {props.link &&
        <a className="walkthrough-link" href={props.link}>
          <span className="fa fa-question-circle icon-with-text-right" />
          {trans('learn-more', {}, 'actions')}
        </a>
      }
    </div>

    {0 !== props.additional.length &&
      <ul className="walkthrough-additional">
        {props.additional.map((walkthrough) =>
          <li key={toKey(walkthrough.title)}>
            <CallbackButton
              className="walkthrough-additional-link"
              callback={() => props.start(walkthrough.scenario, walkthrough.additional, walkthrough.documentation)}
            >
              {walkthrough.title}
            </CallbackButton>
          </li>
        )}
      </ul>
    }

    <div className="walkthrough-btns">
      <CallbackButton
        className="btn"
        callback={props.restart}
      >
        {trans('restart', {}, 'actions')}
      </CallbackButton>

      <CallbackButton
        className="btn"
        callback={props.finish}
        primary={true}
      >
        {trans('finish', {}, 'actions')}
      </CallbackButton>
    </div>
  </Popover>

WalkThroughEnd.propTypes = {
  className: T.string,

  // content
  icon: T.oneOfType([T.string, T.element]),
  title: T.string.isRequired,
  message: T.string.isRequired,
  link: T.string,

  additional: T.arrayOf(T.shape(
    WalkthroughTypes.propTypes
  )),

  // navigation
  start: T.func.isRequired,
  finish: T.func.isRequired,
  restart: T.func.isRequired
}

WalkThroughEnd.defaultProps = {
  additional: []
}

export {
  WalkThroughEnd
}
