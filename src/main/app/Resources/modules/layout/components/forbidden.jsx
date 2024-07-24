import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {now, displayDate} from '#/main/app/intl/date'
import {Alert} from '#/main/app/components/alert'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {PageSimple} from '#/main/app/page'

const LayoutForbidden = (props) => {
  const started = get(props.restrictions, 'dates[0]') && get(props.restrictions, 'dates[0]') < now(false)
  const ended   = get(props.restrictions, 'dates[1]') && get(props.restrictions, 'dates[1]') < now(false)

  return (
    <PageSimple>
      <div className="content-md mt-3">
        <h2 className="h3 text-center">{trans('platform_unavailable_title', {}, 'administration')}</h2>
        <p className="lead text-center">{trans('platform_unavailable_help', {}, 'administration')}</p>

        {props.disabled && props.authenticated &&
          <section className="mb-3">
            <h2 className="h4 text-center">{trans('why_platform_disabled', {}, 'administration')}</h2>

            {props.restrictions.disabled &&
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
                {trans('platform_not_started_desc', {date: displayDate(get(props.restrictions, 'dates[0]'))}, 'administration')}
              </Alert>
            }

            {ended &&
              <Alert
                type="info"
                title={trans('platform_ended_alert', {}, 'administration')}
              >
                {trans('platform_ended_desc', {date: displayDate(get(props.restrictions, 'dates[1]'))}, 'administration')}
              </Alert>
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
    </PageSimple>
  )
}

LayoutForbidden.propTypes = {
  authenticated: T.bool.isRequired,
  disabled: T.bool.isRequired,
  restrictions: T.shape({
    disabled: T.bool,
    dates: T.arrayOf(T.string)
  }),
  reactivate: T.func.isRequired
}

export {
  LayoutForbidden
}
