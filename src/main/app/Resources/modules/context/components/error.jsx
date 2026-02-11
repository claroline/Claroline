import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {PageContent, PageSection} from '#/main/app/page'
import {ContentErrorNotFound} from '#/main/app/content/error/components/not-found'
import {ContentErrorRights} from '#/main/app/content/error/components/rights'
import {ContentErrorUnknown} from '#/main/app/content/error/components/unknown'

import {ContextPage} from '#/main/app/context/components/page'
import {actions as contextActions, selectors as contextSelectors} from '#/main/app/context/store'

/**
 * A component displayed when there is an error while opening a workspace.
 */
const ContextError = ({code}) => {
  const dispatch = useDispatch()

  const contextName = useSelector(contextSelectors.name)
  const contextData = useSelector(contextSelectors.data)
  const contactEmail = useSelector(contextSelectors.contactEmail)

  const backAction = {
    type: LINK_BUTTON,
    label: trans('back_home', {}, 'actions'),
    target: '/', // back to desktop home or public home if user is not authenticated
    exact: true
  }

  let errorComponent
  switch (code) {
    case 'NOT_FOUND':
      errorComponent = (
        <ContentErrorNotFound backAction={backAction} contactEmail={contactEmail} />
      )
      break
    case 'NO_RIGHTS':
      errorComponent = (
        <ContentErrorRights
          contentName={contextName}
          backAction={backAction}
          contactEmail={contactEmail}
          onLogin={() => dispatch(contextActions.reload())}
          onRegister={() => dispatch(contextActions.reload())}
        />
      )
      break
    case 'UNKNOWN_ERROR':
      errorComponent = (
        <ContentErrorUnknown backAction={backAction} contactEmail={contactEmail} />
      )
      break
  }

  return createElement(ContextPage, null,
    <PageContent className="d-flex flex-column" poster={get(contextData, 'poster')}>
      <PageSection className="py-5 my-auto">
        {errorComponent}
      </PageSection>
    </PageContent>
  )
}

ContextError.propTypes = {
  code: T.string.isRequired,
  message: T.string,
  additional: T.any
}

export {
  ContextError
}
