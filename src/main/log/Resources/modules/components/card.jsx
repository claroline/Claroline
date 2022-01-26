import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans, displayDate} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'
import {displayUsername} from '#/main/core/user/utils'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {User as UserTypes} from '#/main/core/user/prop-types'

// TODO : adapt to new log format (only used in analytics for old logs)

const LogCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    className={classes('notification-card', props.className)}
    icon={
      <UserAvatar picture={get(props.data, 'doer.picture')} alt={true} />
    }
    title={
      <span dangerouslySetInnerHTML={{__html: displayUsername(props.data.doer) + ' ' + props.data.description}} />
    }
    subtitle={trans('done_at', {
      date: displayDate(props.data.dateLog, false, true)
    }, 'notification')}
  />

LogCard.propTypes = {
  className: T.string,
  data: T.shape({
    id: T.number.isRequired,
    dateLog: T.string.isRequired,
    doer: T.shape(
      UserTypes.propTypes
    ).isRequired,
    description: T.string
  }).isRequired
}

export {
  LogCard
}
