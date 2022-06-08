import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'

import {transAction} from '#/main/transfer/utils'
import {selectors} from '#/main/transfer/tools/transfer/export/store'

const ExportList = (props) =>
  <ToolPage subtitle={trans('all_exports', {}, 'transfer')}>
    <ListData
      name={selectors.STORE_NAME + '.list'}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `${props.path}/export/history/${row.id}`
      })}
      fetch={{
        url: !isEmpty(props.workspace) ?
          ['apiv2_workspace_transfer_export_list', {workspaceId: props.workspace.id}] :
          ['apiv2_transfer_export_list'],
        autoload: true
      }}
      delete={{
        url: ['apiv2_transfer_export_delete_bulk'],
        disabled: (rows) => -1 === rows.findIndex(row => hasPermission('delete', row))
      }}
      definition={[
        {
          name: 'status',
          type: 'choice',
          label: trans('status'),
          displayed: true,
          options: {
            noEmpty: true,
            choices: {
              pending: trans('pending'),
              in_progress: trans('in_progress'),
              success: trans('success'),
              error: trans('error')
            }
          },
          render: (row) => (
            <span className={classes('label', {
              'label-default': 'pending' === row.status,
              'label-info': 'in_progress' === row.status,
              'label-success': 'success' === row.status,
              'label-danger': 'error' === row.status
            })}>
              {trans(row.status)}
            </span>
          )
        }, {
          name: 'name',
          label: trans('name'),
          displayed: true,
          primary: true,
          placeholder: trans('unnamed_export', {}, 'transfer')
        }, {
          name: 'action',
          type: 'string',
          label: trans('type'),
          calculated: (row) => transAction(row.action),
          displayed: true
        }, {
          name: 'format',
          type: 'choice',
          label: trans('format'),
          options: {
            choices: {
              csv: trans('csv')
            }
          }
        }, {
          name: 'meta.createdAt',
          alias: 'createdAt',
          type: 'date',
          label: trans('creation_date'),
          displayed: true,
          options: {
            time: true
          }
        }, {
          name: 'meta.creator',
          alias: 'creator',
          type: 'user',
          label: trans('creator'),
          displayed: true
        }, {
          name: 'workspace',
          type: 'workspace',
          label: trans('workspace'),
          displayable: isEmpty(props.workspace),
          filterable: isEmpty(props.workspace),
          sortable: false
        }
      ]}
      actions={(rows) => [
        {
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          displayed: hasPermission('edit', rows[0]),
          disabled: 'in_progress' === rows[0].status,
          target: props.path+'/export/history/'+rows[0].id+'/edit',
          group: trans('management'),
          scope: ['object']
        }
      ]}
    />
  </ToolPage>

ExportList.propTypes = {
  path: T.string.isRequired,
  workspace: T.object
}

export {
  ExportList
}
