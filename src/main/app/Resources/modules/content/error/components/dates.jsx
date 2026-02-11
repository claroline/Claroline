import React from 'react'
import {PropTypes as T} from 'prop-types'

import {displayDate, now, trans} from '#/main/app/intl'

import {ContentError} from '#/main/app/content/error/components/error'

/**
 * An error component to display when the content is not already/no longer accessible due to date restrictions.
 */
const ContentErrorDates = ({contentName, primaryAction, backAction, contactEmail, startDate, endDate}) => {
  let notStarted = !!startDate && startDate > now(false)

  return (
    <ContentError
      title={trans(notStarted ? 'error_not_started' : 'error_ended')}
      description={trans(notStarted ? 'error_not_started_desc' : 'error_ended_desc', {
        contentName: `<b>${contentName}</b>`,
        date: `<b>${displayDate(notStarted ? startDate : endDate, true, true)}</b>`
      })}
      help={trans('error_invalid_dates_contact', {contactLink: contactEmail ?
        `(<a href="mailto:${contactEmail}">${contactEmail}</a>)` : ''
      })}
      primaryAction={primaryAction}
      backAction={backAction}
    />
  )
}

ContentErrorDates.propTypes = {
  contentName: T.string.isRequired,
  primaryAction: T.object,
  backAction: T.object,
  contactEmail: T.string,
  startDate: T.string,
  endDate: T.string
}

export {
  ContentErrorDates
}
