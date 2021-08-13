import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import {schemeCategory20c} from 'd3-scale'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {ContentLoader} from '#/main/app/content/components/loader'
import {PageFull} from '#/main/app/page/components/full'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

import {SubscriptionPending} from '#/plugin/cursus/tools/trainings/subscription/components/pending'
import {SubscriptionAll} from '#/plugin/cursus/tools/trainings/subscription/components/all'
import {Routes} from '#/main/app/router/components/routes'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {Quota as QuotaTypes} from '#/plugin/cursus/prop-types'
import {selectors} from '#/plugin/cursus/tools/trainings/subscription/store/selectors'

const SubscriptionPage = (props) => {
  if (isEmpty(props.quota)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('training_loading', {}, 'cursus')}
      />
    )
  }

  return (
    <PageFull
      showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
      path={[].concat(getToolBreadcrumb('trainings', props.currentContext.type, props.currentContext.data), [{
        type: LINK_BUTTON,
        label: trans('subscriptions', {}, 'cursus'),
        target: props.path
      }, {
        type: LINK_BUTTON,
        label: props.quota.id,
        target: props.path
      }])}
      title={props.quota.organization.name}
      toolbar="fullscreen more"
    >
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
                      <h1>Pendings coming soon !</h1>
                      /*<SubscriptionPending
                        name={selectors.PENDING_NAME}
                        path={props.path}
                      />*/
                    )
                  }
                }, {
                  path: '/subscriptions',
                  exact: true,
                  render() {
                    return (
                      <SubscriptionAll
                        name={selectors.LIST_NAME}
                        url={['apiv2_cursus_quota_list_subscriptions', {id: props.quota.id}]}
                        path={props.path}
                        setSubscriptionStatus={props.setSubscriptionStatus}
                      />
                    )
                  }
                }
              ]}
            />
          </div>
        </div>
      </Fragment>
    </PageFull>
  )
}

SubscriptionPage.propTypes = {
  path: T.string.isRequired,
  currentContext: T.shape({
    type: T.oneOf(['desktop']),
    data: T.object
  }).isRequired,
  primaryAction: T.string,
  actions: T.array,
  quota: T.shape(
    QuotaTypes.propTypes
  ),
  setSubscriptionStatus: T.func.isRequired
}

export {
  SubscriptionPage
}
