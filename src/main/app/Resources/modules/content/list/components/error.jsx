import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ContentError} from '#/main/app/content/error'

const ListError = ({code}) => {
  let title
  let description
  switch (code) {
    case 'NOT_FOUND':
      title = trans('error_not_found')
      description = trans('list_error_not_found')
      break
    case 'NO_RIGHTS':
      title = trans('error_no_rights')
      description = trans('list_error_no_rights')
      break
    case 'UNKNOWN_ERROR':
      title = trans('error_unknown')
      description = trans('list_error_unknown')
      break
  }

  return (
    <ContentError
      className="p-4 m-auto"
      title={title}
      description={description}
    />
  )
}

ListError.propTypes = {
  code: T.string.isRequired,
  message: T.string,
  additional: T.any
}

export {
  ListError
}
