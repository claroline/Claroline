import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {UserMicro} from '#/main/core/user/components/micro'
import {displayDate} from '#/main/core/scaffolding/date'

// todo use in announces
// todo use in claco-form

const ContentMeta = props =>
  <div className={classes('content-meta', props.className)}>
    <UserMicro
      className="content-creator"
      link={true}
      {...props.meta.creator}
    />

    <div className="content-dates">
      <span>{trans('created_at', {date: props.meta.created ? displayDate(props.meta.created, false, true) : '-'})}</span>

      <span>{trans('updated_at', {date: props.meta.updated ? displayDate(props.meta.updated, false, true) : '-'})}</span>
    </div>
  </div>

ContentMeta.propTypes = {
  className: T.string,
  meta: T.shape({
    creator: T.shape({

    }),
    created: T.string,
    updated : T.string
  })
}

ContentMeta.defaultProps = {
  meta: {}
}

export {
  ContentMeta
}
