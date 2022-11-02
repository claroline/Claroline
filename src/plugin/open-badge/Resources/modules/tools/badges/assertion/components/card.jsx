import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'

import {DataCard} from '#/main/app/data/components/card'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {displayUsername} from '#/main/community/utils'

import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'

import {Assertion as AssertionTypes} from '#/plugin/open-badge/prop-types'

const AssertionBadgeCard = props =>
  <BadgeCard
    {...props}
    id={props.data.id}
    data={props.data.badge}
    subtitle={trans('granted_at', {
      date: displayDate(get(props.data, 'issuedOn'), false, true)
    }, 'badge')}
  />

AssertionBadgeCard.propTypes = {
  data: T.shape(
    AssertionTypes.propTypes
  ).isRequired
}

const AssertionUserCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    className={classes('notification-card', props.className)}
    icon={
      <UserAvatar picture={get(props.data, 'user.picture')} />
    }
    title={displayUsername(get(props.data, 'user'))}
    subtitle={trans('granted_at', {
      date: displayDate(get(props.data, 'issuedOn'), false, true)
    }, 'badge')}
  />

AssertionUserCard.propTypes = {
  className: T.string,
  data: T.shape(
    AssertionTypes.propTypes
  ).isRequired
}

export {
  AssertionBadgeCard,
  AssertionUserCard
}
