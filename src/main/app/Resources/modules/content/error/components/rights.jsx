import React from 'react'
import {useSelector} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {selectors as platformSelectors} from '#/main/app/platform/store'
import {MODAL_SECURITY, selectors as securitySelectors} from '#/main/app/security'

import {ContentError} from '#/main/app/content/error/components/error'

/**
 * An error component to display when the user doesn't have the permissions to see the content.
 * It displays connection and registration buttons for anonymous users.
 */
const ContentErrorRights = ({contentName, primaryAction, backAction, contactEmail, onLogin, onRegister}) => {
  const selfRegistration = useSelector(platformSelectors.selfRegistration)
  const isAuthenticated = useSelector(securitySelectors.isAuthenticated)

  if (!isAuthenticated) {
    return (
      <ContentError
        title={trans('error_not_authenticated')}
        description={trans('error_not_authenticated_desc', {contentName: `<b>${contentName}</b>`})}
        help={trans('error_no_rights_contact', {contactLink: contactEmail ?
          `(<a href="mailto:${contactEmail}">${contactEmail}</a>)` : ''
        })}
        primaryAction={{
          type: MODAL_BUTTON,
          modal: [MODAL_SECURITY, {
            onLogin: onLogin,
            onRegister: onRegister
          }],
          label: trans('login', {}, 'actions')
        }}
        secondaryAction={{
          type: MODAL_BUTTON,
          modal: [MODAL_SECURITY, {
            page: 'registration',
            onLogin: onLogin,
            onRegister: onRegister
          }],
          label: trans('self_register', {}, 'actions'),
          displayed: selfRegistration
        }}
      />
    )
  }

  return (
    <ContentError
      title={trans('error_no_rights')}
      description={trans('error_no_rights_desc', {contentName: `<b>${contentName}</b>`})}
      help={trans('error_no_rights_contact', {contactLink: contactEmail ?
        `(<a href="mailto:${contactEmail}">${contactEmail}</a>)` : ''
      })}
      primaryAction={primaryAction}
      backAction={backAction}
    />
  )
}

ContentErrorRights.propTypes = {
  contentName: T.string.isRequired,
  primaryAction: T.object,
  backAction: T.object,
  contactEmail: T.string,
  onLogin: T.func,
  onRegister: T.func
}

export {
  ContentErrorRights
}
