import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'

import {UserCard} from '#/main/community/user/components/card'
import {BadgeCard} from '#/plugin/open-badge/badge/components/card'
import {Assertion as AssertionTypes} from '#/plugin/open-badge/prop-types'

const AssertionBadgeCard = props =>
  <BadgeCard
    {...props}
    data={get(props.data, 'badge')}
    contentText={trans('granted_at', {
      date: displayDate(get(props.data, 'issuedOn'), false, true)
    }, 'badge')}
    meta={null}
  />

AssertionBadgeCard.propTypes = {
  data: T.shape(
    AssertionTypes.propTypes
  ).isRequired
}

const AssertionUserCard = props =>
  <UserCard
    {...props}
    data={get(props.data, 'user')}
    contentText={trans('granted_at', {
      date: displayDate(get(props.data, 'issuedOn'), false, true)
    }, 'badge')}
    meta={null}
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
