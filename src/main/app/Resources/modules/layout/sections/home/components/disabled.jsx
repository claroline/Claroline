import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {now, displayDate} from '#/main/app/intl/date'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {ContentHtml} from '#/main/app/content/components/html'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {PageSimple, PageContent} from '#/main/app/page'

const HomeDisabled = (props) => {
  const started = get(props.restrictions, 'dates[0]') && get(props.restrictions, 'dates[0]') < now(false)
  const ended   = get(props.restrictions, 'dates[1]') && get(props.restrictions, 'dates[1]') < now(false)

  return (
    <PageSimple>
      <PageContent>
        <div className="content-md mt-3">
          <h2 className="h3 text-center">{trans('platform_unavailable_title', {}, 'administration')}</h2>
          <p className="lead text-center">{trans('platform_unavailable_help', {}, 'administration')}</p>

          {!props.disabled && props.maintenance && props.maintenanceMessage &&
            <div className="card mb-3">
              <ContentHtml className="card-body">{props.maintenanceMessage}</ContentHtml>
            </div>
          }

          {props.disabled && props.authenticated &&
            <section className="mb-3">
              <h2 className="h4 text-center">{trans('why_platform_disabled', {}, 'administration')}</h2>

              {props.restrictions.disabled &&
                <AlertBlock
                  type="info"
                  title={trans('platform_disabled_alert', {}, 'administration')}
                >
                  {trans('platform_disabled_desc', {}, 'administration')}
                </AlertBlock>
              }

              {!started &&
                <AlertBlock
                  type="info"
                  title={trans('platform_not_started_alert', {}, 'administration')}
                >
                  {trans('platform_not_started_desc', {date: displayDate(get(props.restrictions, 'dates[0]'))}, 'administration')}
                </AlertBlock>
              }

              {ended &&
                <AlertBlock
                  type="info"
                  title={trans('platform_ended_alert', {}, 'administration')}
                >
                  {trans('platform_ended_desc', {date: displayDate(get(props.restrictions, 'dates[1]'))}, 'administration')}
                </AlertBlock>
              }
            </section>
          }

          <hr/>
          {!props.authenticated &&
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
                displayed: !props.authenticated,
                primary: true
              }, {
                name: 'reactivate',
                type: CALLBACK_BUTTON,
                label: trans('reactivate', {}, 'actions'),
                callback: () => props.reactivate(),
                displayed: props.authenticated && !props.restrictions.disabled && ended,
                primary: true
              }
            ]}
          />
        </div>
      </PageContent>
    </PageSimple>
  )
}

HomeDisabled.propTypes = {
  disabled: T.bool.isRequired,
  authenticated: T.bool.isRequired,
  maintenance: T.bool.isRequired,
  maintenanceMessage: T.string,
  restrictions: T.shape({
    disabled: T.bool,
    dates: T.arrayOf(T.string)
  }),
  reactivate: T.func.isRequired
}

export {
  HomeDisabled
}
