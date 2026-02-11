import React, {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import isUndefined from 'lodash/isUndefined'

import {ASYNC_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {PageContent, PageSection} from '#/main/app/page'
import {ContextPage} from '#/main/app/context'
import {ContentErrorNotFound} from '#/main/app/content/error/components/not-found'
import {ContentErrorPublication} from '#/main/app/content/error/components/publication'
import {ContentErrorDates} from '#/main/app/content/error/components/dates'
import {ContentErrorRights} from '#/main/app/content/error/components/rights'
import {ContentErrorCode} from '#/main/app/content/error/components/code'
import {ContentErrorUnknown} from '#/main/app/content/error/components/unknown'

import {actions as contextActions, selectors as contextSelectors} from '#/main/app/context/store'
import {actions} from '#/main/app/contexts/workspace/store'

import {route} from '#/main/core/tool/routing'
import {getRestrictions} from '#/main/core/workspace/utils'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {MODAL_SECURITY} from '#/main/app/security'
import {ContentError} from '#/main/app/content/error'
import {selectors as platformSelectors} from '#/main/app/platform/store'

const WorkspaceErrorNotRegistered = ({contentName, backAction, contactEmail, selfRegistration, registerToWorkspace}) => {
  const platformSelfRegistration = useSelector(platformSelectors.selfRegistration)
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
            onLogin: () => {
              if (selfRegistration) {
                registerToWorkspace()
              }
            },
            onRegister: () => {
              if (selfRegistration) {
                registerToWorkspace()
              }
            }
          }],
          label: trans('login', {}, 'actions')
        }}
        secondaryAction={{
          type: MODAL_BUTTON,
          modal: [MODAL_SECURITY, {
            page: 'registration',
            onLogin: () => {
              if (selfRegistration) {
                registerToWorkspace()
              }
            },
            onRegister: () => {
              if (selfRegistration) {
                registerToWorkspace()
              }
            }
          }],
          label: trans('self_register', {}, 'actions'),
          displayed: platformSelfRegistration
        }}
      />
    )
  }

  return (
    <ContentError
      title={trans('error_not_registered', {}, 'workspace')}
      description={trans(!selfRegistration ? 'error_not_registered_desc' : 'error_not_registered_self_registration_desc', {contentName: `<b>${contentName}</b>`}, 'workspace')}
      help={!selfRegistration ? trans('error_no_rights_contact', {contactLink: contactEmail ?
        `(<a href="mailto:${contactEmail}">${contactEmail}</a>)` : ''
      }) : null}
      primaryAction={{
        type: ASYNC_BUTTON,
        label: trans('restricted_workspace.self_register', {}, 'workspace'),
        async: registerToWorkspace
      }}
      backAction={backAction}
    />
  )
}

/**
 * A component displayed when there is an error while opening a workspace.
 */
const WorkspaceError = ({code, message, additional}) => {
  const dispatch = useDispatch()

  const currentUser = useSelector(securitySelectors.currentUser)
  const contextPath = useSelector(contextSelectors.path)
  const contextName = useSelector(contextSelectors.name)
  const workspace = useSelector(contextSelectors.data)
  const contactEmail = useSelector(contextSelectors.contactEmail)

  const backAction = {
    type: LINK_BUTTON,
    label: trans('back_home', {}, 'actions'),
    target: '/', // back to desktop home or public home if user is not authenticated
    exact: true
  }

  const browseAction = {
    type: LINK_BUTTON,
    label: trans('browse-workspaces', {}, 'actions'),
    target: route('workspaces', contextPath),
    exact: true
  }

  const [restrictions, setRestrictions] = useState(undefined)

  useEffect(() => {
    getRestrictions(workspace, {code: code, message: message, additional: additional}, currentUser).then((pluginRestrictions) => {
      setRestrictions(pluginRestrictions)
    })
  }, [workspace.id])

  if (!isUndefined(restrictions) && 0 !== restrictions.length) {
    return (
      <ContextPage>
        {createElement(restrictions[0].component, {
          path: contextPath,
          workspace: workspace,
          error: {code: code, message: message, additional: additional},
          currentUser: currentUser
        })}
      </ContextPage>
    )
  }

  let errorComponent
  switch (code) {
    case 'NOT_FOUND':
      errorComponent = (
        <ContentErrorNotFound primaryAction={browseAction} backAction={backAction} contactEmail={contactEmail} />
      )
      break
    case 'NOT_REGISTERED':
      errorComponent = (
        <WorkspaceErrorNotRegistered
          contentName={contextName}
          backAction={backAction}
          contactEmail={contactEmail}
          selfRegistration={additional.selfRegistration}
          registerToWorkspace={() => dispatch(actions.selfRegister(workspace))}
        />
      )
      break
    case 'NO_RIGHTS':
      errorComponent = (
        <ContentErrorRights
          contentName={contextName}
          primaryAction={browseAction}
          backAction={backAction}
          contactEmail={contactEmail}
          onLogin={() => dispatch(contextActions.reload())}
          onRegister={() => dispatch(contextActions.reload())}
        />
      )
      break
    case 'NOT_PUBLISHED':
      errorComponent = (
        <ContentErrorPublication
          contentName={contextName}
          primaryAction={browseAction}
          backAction={backAction}
          contactEmail={contactEmail}
          archived={additional.archived}
        />
      )
      break
    case 'INVALID_DATES':
      errorComponent = (
        <ContentErrorDates
          contentName={contextName}
          primaryAction={browseAction}
          backAction={backAction}
          contactEmail={contactEmail}
          startDate={additional.startDate}
          endDate={additional.endDate}
        />
      )
      break
    case 'ACCESS_CODE':
      errorComponent = (
        <ContentErrorCode
          contentName={contextName}
          backAction={backAction}
          contactEmail={contactEmail}
          submitAccessCode={(code) => dispatch(actions.checkAccessCode(workspace, code))}
        />
      )
      break
    case 'UNKNOWN_ERROR':
      errorComponent = (
        <ContentErrorUnknown primaryAction={browseAction} backAction={backAction} contactEmail={contactEmail} />
      )
      break
  }

  return createElement(ContextPage, null,
    <PageContent className="d-flex flex-column" poster={get(workspace, 'poster')}>
      <PageSection className="py-5 my-auto">
        {errorComponent}
      </PageSection>
    </PageContent>
  )
}

WorkspaceError.propTypes = {
  code: T.string.isRequired,
  message: T.string,
  additional: T.any
}

export {
  WorkspaceError
}
