import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {displayDate, trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {UserMicro} from '#/main/core/user/components/micro'
import {ToolPage} from '#/main/core/tool/containers/page'

import {transAction} from '#/main/transfer/utils'
import {Toolbar} from '#/main/app/action'

const TransferDetails = props => {
  if (isEmpty(props.transferFile)) {
    return (
      <ContentLoader
        className="row"
        size="lg"
        description={trans('loading', {}, 'transfer')}
      />
    )
  }

  return (
    <ToolPage
      subtitle={
        <Fragment>
          <span className={classes('label icon-with-text-right', {
            'label-default': 'pending' === props.transferFile.status,
            'label-info': 'in_progress' === props.transferFile.status,
            'label-success': 'success' === props.transferFile.status,
            'label-danger': 'error' === props.transferFile.status
          })}>
            {trans(props.transferFile.status)}
          </span>

          {props.transferFile.name || transAction(props.transferFile.action)}
        </Fragment>
      }
      primaryAction="edit"
      actions={[
        {
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          displayed: hasPermission('edit', props.transferFile),
          disabled: 'in_progress' === props.transferFile.status,
          target: props.path+'/edit',
          primary: true,
          group: trans('management')
        }
      ]}
    >
      <div className="row">
        <div className="col-md-3">
          <div className="panel panel-default" style={{marginTop: 20}}>
            <div className="panel-heading">
              <UserMicro {...get(props.transferFile, 'meta.creator', {})} link={true} />
            </div>

            <ul className="list-group list-group-values">
              <li className="list-group-item">
                {trans('action')}
                <span className="value">{transAction(props.transferFile.action)}</span>
              </li>

              <li className="list-group-item">
                {trans('creation_date')}
                <span className="value">{displayDate(get(props.transferFile, 'meta.createdAt'), false, true)}</span>
              </li>

              <li className="list-group-item">
                {trans('execution_date')}
                <span className="value">
                  {get(props.transferFile, 'executionDate') ? displayDate(get(props.transferFile, 'executionDate'), false, true) : '-'}
                </span>
              </li>

              {get(props.transferFile, 'scheduler.scheduledDate') &&
                <li className="list-group-item">
                  {trans('scheduled_date', {}, 'scheduler')}
                  <span className="value">
                    {displayDate(get(props.transferFile, 'scheduler.scheduledDate'), false, true)}
                  </span>
                </li>
              }
            </ul>
          </div>

          <Toolbar
            buttonName="btn btn-block"
            actions={props.actions}
          />
        </div>

        <div className="col-md-9">
          {props.children}
        </div>
      </div>
    </ToolPage>
  )
}

TransferDetails.propTypes = {
  path: T.string.isRequired,
  transferFile: T.shape({
    name: T.string,
    action: T.string,
    status: T.string.isRequired,
    scheduler: T.shape({
      scheduledDate: T.string.isRequired
    })
  }),
  actions: T.arrayOf(T.object),
  children: T.any
}

export {
  TransferDetails
}
