import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {UserMicro} from '#/main/core/user/components/micro'
import {User as UserTypes} from '#/main/community/prop-types'

// todo use in announces
// todo use in claco-form

const ContentMeta = props =>
  <div className={classes('content-meta', props.className)}>
    <UserMicro
      className="content-creator"
      link={true}
      {...props.creator}
    />

    <div className="content-dates">
      <span>{trans('created_at', {date: props.created ? displayDate(props.created, false, true) : '-'})}</span>

      <span>{trans('updated_at', {date: props.updated ? displayDate(props.updated, false, true) : '-'})}</span>
    </div>
  </div>

ContentMeta.propTypes = {
  className: T.string,
  creator: T.shape(
    UserTypes.propTypes
  ),
  created: T.string,
  updated : T.string
}

export {
  ContentMeta
}
