import React from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {EmptyState} from '#/main/app/components/empty-state'
import {PageContent} from '#/main/app/page'
import {ContextPage} from '#/main/app/context'
import {MODAL_SECURITY} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as contextActions} from '#/main/app/context/store'

const DesktopForbidden = () => {
  const dispatch = useDispatch()
  const authenticated = useSelector(securitySelectors.isAuthenticated)

  return (
    <ContextPage>
      <PageContent className="d-flex flex-column">
        <EmptyState
          className="p-4"
          icon="fa fa-lock"
          title={trans('access_forbidden', {}, 'desktop')}
          description={trans('access_forbidden_help', {}, 'desktop')}
          primaryAction={{
            type: MODAL_BUTTON,
            label: trans('login', {}, 'actions'),
            displayed: !authenticated,
            modal: [MODAL_SECURITY, {
              onLogin: () => dispatch(contextActions.reload()),
              onRegister: () => dispatch(contextActions.reload())
            }]
          }}
          secondaryAction={{
            type: LINK_BUTTON,
            icon: 'fa fa-arrow-left',
            label: trans('back_home', {}, 'actions'),
            target: '/',
            exact: true
          }}
        />
      </PageContent>
    </ContextPage>
  )
}

export {
  DesktopForbidden
}
