import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListSource} from '#/main/app/content/list/containers/source'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {actions} from '#/plugin/claco-form/resources/claco-form/player/store'
import {constants} from '#/plugin/claco-form/resources/claco-form/constants'
import {ClacoForm as ClacoFormTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'
import entriesSource from '#/plugin/claco-form/data/sources/entries'
import {
  canEditEntry,
  canManageEntry
} from '#/plugin/claco-form/resources/claco-form/permissions'
import {ResourcePage} from '#/main/core/resource'

const EntriesComponent = props =>
  <ResourcePage
    title={trans('entries_list', {}, 'clacoform')}
  >
    <ListSource
      className="mt-3"
      name={selectors.STORE_NAME+'.entries.list'}
      fetch={{
        url: ['apiv2_clacoformentry_list', {clacoForm: props.clacoForm.id}],
        autoload: true
      }}
      delete={{
        url: ['apiv2_clacoformentry_delete'],
        displayed: (rows) => rows.filter(e => !e.locked && canManageEntry(e, props.canEdit, props.currentUser)).length === rows.length
      }}
      source={merge({}, entriesSource(props.clacoForm, props.canViewMetadata, props.canEdit, props.isCategoryManager, props.path, props.currentUser), {
        actions: (rows) => [
          {
            name: 'edit',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            target: `${props.path}/entry/form/${rows[0].id}`,
            displayed: !rows[0].locked && canEditEntry(rows[0], props.clacoForm, props.currentUser),
            scope: ['object'],
            group: trans('management'),
            primary: true
          }, {
            name: 'export-pdf',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-file-pdf',
            label: trans('export-pdf', {}, 'actions'),
            displayed: true,
            callback: () => {
              if (1 < rows.length) {
                // collection
                rows.forEach(row => props.downloadEntryPdf(row.id))
              } else {
                // object
                props.downloadEntryPdf(rows[0].id)
              }
            },
            scope: ['object', 'collection'],
            group: trans('transfer')
          }, {
            name: 'publish',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-eye',
            label: trans('publish', {}, 'actions'),
            callback: () => props.switchEntriesStatus(rows, constants.ENTRY_STATUS_PUBLISHED),
            displayed: rows.filter(e => !e.locked && canManageEntry(e, props.canEdit, props.currentUser)).length === rows.length &&
              rows.filter(e => e.status === constants.ENTRY_STATUS_PUBLISHED).length !== rows.length,
            group: trans('management')
          }, {
            name: 'unpublish',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-eye-slash',
            label: trans('unpublish', {}, 'actions'),
            callback: () => props.switchEntriesStatus(rows, constants.ENTRY_STATUS_UNPUBLISHED),
            displayed: rows.filter(e => !e.locked && canManageEntry(e, props.canEdit, props.currentUser)).length === rows.length &&
            rows.filter(e => e.status !== constants.ENTRY_STATUS_PUBLISHED).length !== rows.length,
            group: trans('management')
          }, {
            name: 'lock',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-lock',
            label: trans('lock', {}, 'actions'),
            callback: () => props.switchEntriesLock(rows, true),
            displayed: -1 !== rows.findIndex(e => !e.locked && canManageEntry(e, props.canEdit, props.currentUser)),
            group: trans('management')
          }, {
            name: 'unlock',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-unlock',
            label: trans('unlock', {}, 'actions'),
            callback: () => props.switchEntriesLock(rows, false),
            displayed: -1 !== rows.findIndex(e => e.locked && canManageEntry(e, props.canEdit, props.currentUser)),
            group: trans('management')
          }
        ]
      })}
      parameters={props.listConfiguration}
    />
  </ResourcePage>

EntriesComponent.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  clacoForm: T.shape(
    ClacoFormTypes.propTypes
  ).isRequired,
  listConfiguration: T.shape(
    ListParametersTypes.propTypes
  ),
  canEdit: T.bool.isRequired,
  canViewMetadata: T.bool.isRequired,
  isCategoryManager: T.bool.isRequired,
  canGeneratePdf: T.bool.isRequired,

  downloadEntryPdf: T.func.isRequired,
  switchEntriesStatus: T.func.isRequired,
  switchEntriesLock: T.func.isRequired
}

const Entries = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    listConfiguration: selectors.listConfiguration(state),
    clacoForm: selectors.clacoForm(state),
    canEdit: selectors.canEdit(state),
    canViewMetadata: selectors.canViewMetadata(state),
    canGeneratePdf: selectors.canGeneratePdf(state),
    isCategoryManager: selectors.isCategoryManager(state)
  }),
  (dispatch) => ({
    downloadEntryPdf(entryId) {
      return dispatch(actions.downloadEntryPdf(entryId))
    },
    switchEntriesStatus(entries, status) {
      dispatch(actions.switchEntriesStatus(entries, status))
    },
    switchEntriesLock(entries, locked) {
      dispatch(actions.switchEntriesLock(entries, locked))
    }
  })
)(EntriesComponent)

export {
  Entries
}
