import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'

import {Quota as QuotaTypes} from '#/plugin/cursus/prop-types'
import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router/components/routes'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {QuotaPage} from '#/plugin/cursus/quota/components/page'

const ValidationDetail = (props) => (
  <QuotaPage
    basePath={props.path}
    currentContext={props.currentContext}
    quota={props.quota}
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
            basePath={props.quota ? props.path+'/'+props.quota.id : props.path}
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
            path={props.quota ? props.path+'/'+props.quota.id : props.path}
            routes={[
              {
                path: '/',
                exact: true,
                render() {
                  return (
                    <h1>Pending</h1>
                  )
                }
              }, {
                path: '/subscriptions',
                exact: true,
                render() {
                  return (
                    <h1>Subscriptions</h1>
                  )
                }
              }
            ]}
          />
        </div>
      </div>
    </Fragment>
  </QuotaPage>
)

ValidationDetail.propTypes = {
  path: T.string.isRequired,
  quota: T.shape(
    QuotaTypes.propTypes
  )
}

export {
  ValidationDetail
}