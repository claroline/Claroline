import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'

import {ToolPage} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {ListData} from '#/main/app/content/list/containers/data'

import {transAction} from '#/main/transfer/utils'
import {selectors} from '#/main/transfer/tools/export/store'
import {PageListSection} from '#/main/app/page/components/list-section'

const ExportList = (props) =>
  <ToolPage
    title={trans('all_exports', {}, 'transfer')}
  >
    <PageListSection>
      <ListData
        flush={true}
        name={selectors.LIST_NAME}
        addAction={{
          name: 'add',
          type: LINK_BUTTON,
          label: trans('new_export', {}, 'transfer'),
          target: `${props.path}/new`,
          group: trans('management'),
          displayed: props.canExport,
          primary: true
        }}
        primaryAction={(row) => ({
          type: LINK_BUTTON,
          target: `${props.path}/${row.id}`
        })}
        fetch={{
          url: !isEmpty(props.workspace) ?
            ['apiv2_transfer_export_workspace_list', {workspaceId: props.workspace.id}] :
            ['apiv2_transfer_export_list'],
          autoload: true
        }}
        delete={{
          url: ['apiv2_transfer_export_delete'],
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
              <span className={classes('badge', {
                'text-bg-secondary': 'pending' === row.status,
                'text-bg-info': 'in_progress' === row.status,
                'text-bg-success': 'success' === row.status,
                'text-bg-danger': 'error' === row.status
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
            name: 'executionDate',
            alias: 'executedAt',
            type: 'date',
            label: trans('execution_date'),
            displayed: true,
            options: {
              time: true
            }
          }, {
            name: 'meta.createdAt',
            alias: 'createdAt',
            type: 'date',
            label: trans('creation_date'),
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
            target: props.path+'/'+rows[0].id+'/edit',
            group: trans('management'),
            scope: ['object']
          }
        ]}
      />
    </PageListSection>
  </ToolPage>

ExportList.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  canExport: T.bool
}

export {
  ExportList
}
