import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'
import {trans, displayDate} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'
import {UserMicroList} from '#/main/core/user/components/micro-list'

import {Session as SessionTypes} from '#/plugin/cursus/prop-types'

const SessionCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    className={classes(props.className, {
      'data-card-muted': get(props.data, 'restrictions.hidden', false)
    })}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon="fa fa-calendar-week"
    title={trans('date_range', {
      start: displayDate(props.data.restrictions.dates[0]),
      end: displayDate(props.data.restrictions.dates[1])
    })}
    subtitle={props.data.name}
    contentText={props.data.plainDescription || props.data.description}
    toolbar="self-register | more"
    footer={
      <span
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        {props.data.tutors && 0 !== props.data.tutors.length &&
          <UserMicroList
            id={`session-tutors-${props.data.id}`}
            label={trans('tutors', {}, 'cursus')}
            users={props.data.tutors}
          />
        }

        {get(props.data, 'location.name') || trans('online_session', {}, 'cursus')}
      </span>
    }
    flags={[
      get(props.data, 'restrictions.hidden') && ['fa fa-eye-slash', trans('session_hidden', {}, 'workspace')]
    ].filter(flag => !!flag)}
  />

SessionCard.propTypes = {
  className: T.string,
  data: T.shape(
    SessionTypes.propTypes
  ).isRequired
}

export {
  SessionCard
}
