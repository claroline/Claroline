import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'

import {ContentError} from '#/main/app/content/error/components/error'

/**
 * An error component to display when a content is not published or is archived.
 */
const ContentErrorPublication = ({contentName, primaryAction, backAction, contactEmail, archived = false}) => {
  return (
    <ContentError
      title={trans(archived ? 'error_archived' : 'error_not_published')}
      description={trans(archived ? 'error_archived_desc' : 'error_not_published_desc', {contentName: `<b>${contentName}</b>`})}
      help={trans('error_publication_contact', {contactLink: contactEmail ?
        `(<a href="mailto:${contactEmail}">${contactEmail}</a>)` : ''
      })}
      primaryAction={primaryAction}
      backAction={backAction}
    />
  )
}

ContentErrorPublication.propTypes = {
  contentName: T.string.isRequired,
  primaryAction: T.object,
  backAction: T.object,
  contactEmail: T.string,
  archived: T.bool
}

export {
  ContentErrorPublication
}
