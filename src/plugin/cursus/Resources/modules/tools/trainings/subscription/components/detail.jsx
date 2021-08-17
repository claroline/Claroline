import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'

import {LINK_BUTTON} from '#/main/app/buttons'
import {Quota as QuotaTypes} from '#/plugin/cursus/prop-types'
import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router/components/routes'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {QuotaPage} from '#/plugin/cursus/quota/components/page'
import {SubscriptionPending} from '#/plugin/cursus/tools/trainings/subscription/components/pending'
import {SubscriptionAll} from '#/plugin/cursus/tools/trainings/subscription/components/all'
import {selectors} from '#/plugin/cursus/tools/trainings/quota/store/selectors'

const SubscriptionDetail = (props) => (
  <QuotaPage
    basePath={props.path}
    path={[{
      type: LINK_BUTTON,
      label: trans('subscriptions', {}, 'cursus'),
      target: props.path
    }, {
      type: LINK_BUTTON,
      label: 'test',
      target: props.path
    }]}
    currentContext={props.currentContext}
    quota={props.quota}
  >
    {
      props.quota &&
      <Fragment>
        <div className="row">
          <div className="analytics-card">
            <span className="fa fa-chalkboard-teacher" style={{backgroundColor: schemeCategory20c[1]}} />
            <h1 className="h3">
              <small>{trans('tutors', {}, 'cursus')}</small>
            </h1>
          </div>
        </div>

        <div className="row">
          <div className="col-md-3">
            <Vertical
              basePath={props.path+'/'+props.quota.id}
              tabs={[
                {
                  icon: 'fa fa-fw fa-chalkboard-teacher',
                  title: trans('to_subscribe', {}, 'cursus'),
                  path: '/',
                  exact: true
                }, {
                  icon: 'fa fa-fw fa-user',
                  title: trans('subscriptions'),
                  path: '/subscriptions',
                  exact: true
                }
              ]}
            />
          </div>

          <div className="col-md-9">
            <Routes
              path={props.path+'/'+props.quota.id}
              routes={[
                {
                  path: '/',
                  exact: true,
                  render() {
                    return (
                      <SubscriptionPending
                        name={selectors.LIST_NAME}
                        path={props.path}
                      />
                    )
                  }
                }, {
                  path: '/subscriptions',
                  exact: true,
                  render() {
                    return (
                      <SubscriptionAll
                        name={selectors.LIST_NAME}
                        path={props.path}
                      />
                    )
                  }
                }
              ]}
            />
          </div>
        </div>
      </Fragment>
    }
  </QuotaPage>
)

SubscriptionDetail.propTypes = {
  currentContext: T.shape({
    type: T.oneOf(['desktop']),
    data: T.object
  }).isRequired,
  path: T.string.isRequired,
  quota: T.shape(
    QuotaTypes.propTypes
  )
}

export {
  SubscriptionDetail
}