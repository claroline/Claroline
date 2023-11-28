import React from 'react'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'

import {trans}   from '#/main/app/intl'
import {getIcon, getRule} from '#/plugin/flashcard/resources/flashcard/utils'

const SessionRule= props => {
  const icon = getIcon(props.index)
  const rule = getRule(props.index)

  let classList = ['icon-with-text-right']
  if (props.index < props.session || (props.index === props.session && props.completed)) {
    classList.push('session-rule-done')
  }

  return (
    <li className="session-rule mt-1">
      <div className={classes(classList)}>
        {icon}
      </div>
      <div className="">
        Session {props.index} : {trans('session_cards_start', {}, 'flashcard') + ' ' + rule}.
      </div>
    </li>
  )
}

SessionRule.propTypes = {
  session: T.number,
  rule: T.number,
  completed: T.bool,
  index: T.number
}

const LeitnerRules = (props) => (
  <ul
    className="sessions-rules mb-3">
    {Array.from({length: 7}, (_, index) => (
      <SessionRule
        key={index}
        session={props.session}
        completed={props.completed}
        index={index + 1}
      />
    ))}
  </ul>
)

LeitnerRules.propTypes = {
  session: T.number,
  completed: T.bool
}

export {
  LeitnerRules
}
