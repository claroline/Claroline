import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'

import {ContentError} from '#/main/app/content/error/components/error'

/**
 * An error component to display when an unknown error occurs during the content loading.
 */
const ContentErrorUnknown = ({primaryAction, backAction, contactEmail}) => {
  return (
    <ContentError
      title={trans('error_unknown')}
      description={trans('error_unknown_desc')}
      help={trans('error_unknown_contact', {contactLink: contactEmail ?
        `(<a href="mailto:${contactEmail}">${contactEmail}</a>)` : ''
      })}
      primaryAction={primaryAction}
      backAction={backAction}
    />
  )
}

ContentErrorUnknown.propTypes = {
  primaryAction: T.object,
  backAction: T.object,
  contactEmail: T.string
}

export {
  ContentErrorUnknown
}
