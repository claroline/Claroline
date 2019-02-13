import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListSource} from '#/main/app/content/list/containers/source'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {actions} from '#/plugin/claco-form/resources/claco-form/player/store'
import {constants} from '#/plugin/claco-form/resources/claco-form/constants'
import {ClacoForm as ClacoFormTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'
import entriesSource from '#/plugin/claco-form/data/sources/entries'
import {
  canEditEntry,
  canManageEntry
} from '#/plugin/claco-form/resources/claco-form/permissions'

const EntriesComponent = props =>
  <ListSource
    title={trans('entries_list', {}, 'clacoform')}
    name={selectors.STORE_NAME+'.entries.list'}
    fetch={{
      url: ['apiv2_clacoformentry_list', {clacoForm: props.clacoForm.id}],
      autoload: true
    }}

    source={merge({}, entriesSource(props.clacoForm, props.canViewMetadata, props.canEdit, props.canAdministrate, props.isCategoryManager), {
      parameters: {
        actions: (rows) => [
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-print',
            label: trans('print_entry', {}, 'clacoform'),
            displayed: props.canGeneratePdf,
            callback: () => {
              if (1 < rows.length) {
                // collection
                props.downloadEntriesPdf(rows)
              } else {
                // object
                props.downloadEntryPdf(rows[0].id)
              }
            },
            scope: ['object', 'collection'],
            group: trans('transfer')
          }, {
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            target: `/entry/form/${rows[0].id}`,
            displayed: !rows[0].locked && canEditEntry(rows[0], props.clacoForm),
            scope: ['object'],
            group: trans('management')
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-eye',
            label: trans('publish', {}, 'actions'),
            callback: () => props.switchEntriesStatus(rows, constants.ENTRY_STATUS_PUBLISHED),
            displayed: rows.filter(e => !e.locked && canManageEntry(e, props.canEdit)).length === rows.length &&
            rows.filter(e => e.status === constants.ENTRY_STATUS_PUBLISHED).length !== rows.length,
            group: trans('management')
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-eye-slash',
            label: trans('unpublish', {}, 'actions'),
            callback: () => props.switchEntriesStatus(rows, constants.ENTRY_STATUS_UNPUBLISHED),
            displayed: rows.filter(e => !e.locked && canManageEntry(e, props.canEdit)).length === rows.length &&
            rows.filter(e => e.status !== constants.ENTRY_STATUS_PUBLISHED).length !== rows.length,
            group: trans('management')
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-w fa-lock',
            label: trans('lock', {}, 'actions'),
            callback: () => props.switchEntriesLock(rows, true),
            displayed: props.canAdministrate && rows.filter(e => e.locked).length !== rows.length,
            group: trans('management')
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-w fa-unlock',
            label: trans('unlock', {}, 'actions'),
            callback: () => props.switchEntriesLock(rows, false),
            displayed: props.canAdministrate && rows.filter(e => !e.locked).length !== rows.length,
            group: trans('management')
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-w fa-trash-o',
            label: trans('delete', {}, 'actions'),
            confirm: {
              title: transChoice('delete_selected_entries', rows.length, {count: rows.length}, 'clacoform'),
              message: transChoice('delete_selected_entries_confirm_message', rows.length, {count: rows.length}, 'clacoform')
            },
            callback: () => props.deleteEntries(rows),
            displayed: rows.filter(e => !e.locked && canManageEntry(e, props.canEdit)).length === rows.length,
            dangerous: true
          }
        ]
      }
    })}
    parameters={props.listConfiguration}
  />

EntriesComponent.propTypes = {
  clacoForm: T.shape(
    ClacoFormTypes.propTypes
  ).isRequired,
  listConfiguration: T.shape(
    ListParametersTypes.propTypes
  ),
  canEdit: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  canViewMetadata: T.bool.isRequired,
  isCategoryManager: T.bool.isRequired,
  canGeneratePdf: T.bool.isRequired,

  downloadEntryPdf: T.func.isRequired,
  downloadEntriesPdf: T.func.isRequired,
  switchEntriesStatus: T.func.isRequired,
  switchEntriesLock: T.func.isRequired,
  deleteEntries: T.func.isRequired,
  downloadFieldValueFile: T.func.isRequired
}

const Entries = connect(
  (state) => ({
    listConfiguration: selectors.listConfiguration(state),
    clacoForm: selectors.clacoForm(state),
    canEdit: selectors.canEdit(state),
    canViewMetadata: selectors.canViewMetadata(state),
    canAdministrate: selectors.canAdministrate(state),
    canGeneratePdf: selectors.canGeneratePdf(state),
    isCategoryManager: selectors.isCategoryManager(state)
  }),
  (dispatch) => ({
    downloadEntryPdf(entryId) {
      dispatch(actions.downloadEntryPdf(entryId))
    },
    downloadEntriesPdf(entries) {
      dispatch(actions.downloadEntriesPdf(entries))
    },
    switchEntriesStatus(entries, status) {
      dispatch(actions.switchEntriesStatus(entries, status))
    },
    switchEntriesLock(entries, locked) {
      dispatch(actions.switchEntriesLock(entries, locked))
    },
    deleteEntries(entries) {
      dispatch(actions.deleteEntries(entries))
    },
    downloadFieldValueFile(fieldValueId) {
      dispatch(actions.downloadFieldValueFile(fieldValueId))
    }
  })
)(EntriesComponent)

export {
  Entries
}
