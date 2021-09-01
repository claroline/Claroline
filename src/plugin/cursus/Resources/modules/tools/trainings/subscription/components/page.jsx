import React, {Fragment, useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {ContentLoader} from '#/main/app/content/components/loader'
import {PageFull} from '#/main/app/page/components/full'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

import {SubscriptionAll} from '#/plugin/cursus/tools/trainings/subscription/components/all'
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

  useEffect(() => {
    if (!isEmpty(props.quota)) {
      props.getStatistics(props.quota.id)
    }
  }, [props.quota])

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
      actions={[{
        name: 'export-pdf',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-file-pdf-o',
        label: trans('export-pdf', {}, 'actions'),
        group: trans('transfer'),
        target: ['apiv2_cursus_quota_export', {id: props.quota.id, filters: props.filters}],
        displayed: true
      }]}
    >
      <Fragment>
        <div className="row">
          <div className="analytics-card">
            <span className="fa fa-chalkboard-teacher label-info" />
            <h1 className="h3">
              <small>{trans('subscription_total', {}, 'cursus')}</small>
              {props.statistics.total}
            </h1>
          </div>
          <div className="analytics-card">
            <span className="fa fa-pause label-default" />
            <h1 className="h3">
              <small>{trans('subscription_pending', {}, 'cursus')}</small>
              {props.statistics.pending}
            </h1>
          </div>
          <div className="analytics-card">
            <span className="fa fa-times label-danger" />
            <h1 className="h3">
              <small>{trans('subscription_refused', {}, 'cursus')}</small>
              {props.statistics.refused}
            </h1>
          </div>
          <div className="analytics-card">
            <span className="fa fa-check label-warning" />
            <h1 className="h3">
              <small>{trans('subscription_validated', {}, 'cursus')}</small>
              {props.statistics.validated}
            </h1>
          </div>
          {props.quota.useQuotas && props.statistics.calculated != undefined &&
            <div className="analytics-card">
              <span className="fa fa-check-double label-success" />
              <h1 className="h3">
                <small>{trans('subscription_managed', {}, 'cursus')}</small>
                {props.statistics.managed}
              </h1>
            </div>
          }
          {props.quota.useQuotas && props.statistics.calculated != undefined &&
            <div className="analytics-card">
              <span className="fa fa-chart-pie label-primary" />
              <h1 className="h3">
                <small>{trans('subscription_quota', {}, 'cursus')}</small>
                {props.statistics.calculated.toFixed(2)} / {get(props.quota, 'threshold')} 
              </h1>
            </div>
          }
        </div>

        <div className="row">
          <div className="col-md-12">
            <SubscriptionAll
              name={selectors.LIST_NAME}
              url={['apiv2_cursus_quota_list_subscriptions', {id: props.quota.id}]}
              path={props.path}
              setSubscriptionStatus={props.setSubscriptionStatus}
              quota={props.quota}
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
  filters: T.shape({}).isRequired, // Add PropType for filters
  statistics: T.shape({
    total: T.number,
    pending: T.number,
    refused: T.number,
    validated: T.number,
    managed: T.number,
    calculated: T.number
  }).isRequired,
  getStatistics: T.func.isRequired,
  setSubscriptionStatus: T.func.isRequired
}

export {
  SubscriptionPage
}
