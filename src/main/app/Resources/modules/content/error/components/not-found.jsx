import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ContentError} from '#/main/app/content/error/components/error'

/**
 * An error component to display when a request content cannot be found.
 */
const ContentErrorNotFound = ({primaryAction, backAction, contactEmail}) => {
  return (
    <ContentError
      title={trans('error_not_found')}
      description={trans('error_not_found_desc')}
      help={trans('error_not_found_contact', {contactLink: contactEmail ?
        `(<a href="mailto:${contactEmail}">${contactEmail}</a>)` : ''
      })}
      primaryAction={primaryAction}
      backAction={backAction}
    />
  )
}

ContentError.propTypes = {
  primaryAction: T.object,
  backAction: T.object,
  contactEmail: T.string
}

export {
  ContentErrorNotFound
}
