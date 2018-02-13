import React from 'react'

import {t} from '#/main/core/translation'
import {displayDate} from '#/main/core/scaffolding/date'

import {UserAvatar} from '#/main/core/user/components/avatar.jsx'

const UserCard = (row) => ({
  icon: <UserAvatar picture={row.picture} alt={true} />,
  title: row.username,
  subtitle: row.firstName + ' ' + row.lastName,
  contentText: row.meta.description,
  flags: [
    row.meta.personalWorkspace && ['fa fa-book', t('has_personal_workspace')],
    !row.restrictions.disabled && ['fa fa-check-circle-o', t('user_enabled')] // todo also checks accessibility dates
  ].filter(flag => !!flag),
  footer:
    row.meta.lastLogin &&
    <span>
      {t('last_logged_at')} <b>{displayDate(row.meta.lastLogin, false, true)}</b>
    </span>,
  footerLong:
    <span>
      {t('registered_at')} <b>{displayDate(row.meta.created)}</b>
      {row.meta.lastLogin &&
        <span>, {t('last_logged_at')} <b>{displayDate(row.meta.lastLogin, false, true)}</b></span>
      }
    </span>
})

export {
  UserCard
}
