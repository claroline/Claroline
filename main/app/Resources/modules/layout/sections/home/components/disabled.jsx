import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {now, displayDate} from '#/main/app/intl/date'
import {Alert} from '#/main/app/alert/components/alert'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {PageSimple} from '#/main/app/page/components/simple'
import {ContentHelp} from '#/main/app/content/components/help'

const HomeDisabled = (props) => {
  const started = get(props.restrictions, 'dates[0]') && get(props.restrictions, 'dates[0]') < now()
  const ended   = get(props.restrictions, 'dates[1]') && get(props.restrictions, 'dates[1]') < now()

  return (
    <PageSimple>
      <div className="page-content app-disabled">
        <h1 className="page-title text-center">
          <span className="fa fa-power-off text-danger" />
          {trans('platform_unavailable_title', {}, 'administration')}
          <small>
            {trans('platform_unavailable_help', {}, 'administration')}
          </small>
        </h1>

        {!props.disabled && props.maintenance && props.maintenanceMessage &&
          <HtmlText>{props.maintenanceMessage}</HtmlText>
        }

        {props.disabled && props.authenticated &&
          <section>
            <h2 className="text-center">{trans('why_platform_disabled', {}, 'administration')}</h2>

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

        <Toolbar
          buttonName="btn btn-block btn-emphasis"
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

        {!props.authenticated &&
          <ContentHelp help={trans('only_admin_login_help', {}, 'administration')} />
        }
      </div>
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
