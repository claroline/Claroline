import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {now, displayDate} from '#/main/app/intl/date'
import {Alert} from '#/main/app/components/alert'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON} from '#/main/app/buttons'
import {PageContent, PageSimple} from '#/main/app/page'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as configSelectors} from '#/main/app/config/store'

import {selectors} from '#/main/app/platform/store'

const PlatformForbidden = () => {
  const authenticated = useSelector(securitySelectors.isAuthenticated)
  const disabled = useSelector(selectors.unavailable)
  const restrictions = useSelector((state) => configSelectors.param(state, 'restrictions'))

  const started = get(restrictions, 'dates[0]') && get(restrictions, 'dates[0]') < now(false)
  const ended   = get(restrictions, 'dates[1]') && get(restrictions, 'dates[1]') < now(false)

  return (
    <PageSimple>
      <PageContent className="d-flex flex-column justify-content-center">
        <div className="content-lg my-5">
          <h2 className="h3 text-center">{trans('platform_unavailable_title', {}, 'administration')}</h2>
          <p className="lead text-center">{trans('platform_unavailable_help', {}, 'administration')}</p>

          {disabled && authenticated &&
            <section className="mb-3">
              <h2 className="h4 text-center">{trans('why_platform_disabled', {}, 'administration')}</h2>

              {restrictions.disabled &&
                <Alert
                  type="info"
                  title={trans('platform_disabled_alert', {}, 'administration')}
                >
                  {trans('platform_disabled_desc', {}, 'administration')}
                </Alert>
              }

              {!started &&
                <Alert
                  type="info"
                  title={trans('platform_not_started_alert', {}, 'administration')}
                >
                  {trans('platform_not_started_desc', {date: displayDate(get(restrictions, 'dates[0]'))}, 'administration')}
                </Alert>
              }

              {ended &&
                <Alert
                  type="info"
                  title={trans('platform_ended_alert', {}, 'administration')}
                >
                  {trans('platform_ended_desc', {date: displayDate(get(restrictions, 'dates[1]'))}, 'administration')}
                </Alert>
              }
            </section>
          }

          <hr/>

          {!authenticated &&
            <p className="text-secondary">
              {trans('only_admin_login_help', {}, 'administration')}
            </p>
          }

          <Toolbar
            buttonName="w-100 mb-3"
            variant="btn"
            size="lg"
            actions={[
              {
                name: 'login',
                type: LINK_BUTTON,
                label: trans('login', {}, 'actions'),
                target: '/login',
                displayed: !authenticated,
                primary: true
              }
            ]}
          />
        </div>
      </PageContent>
    </PageSimple>
  )
}

export {
  PlatformForbidden
}
