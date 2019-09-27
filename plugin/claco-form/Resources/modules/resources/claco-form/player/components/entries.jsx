import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import merge from 'lodash/merge'

// TODO : avoid hard dependency
import html2pdf from 'html2pdf.js'

import {trans, transChoice} from '#/main/app/intl/translation'
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

const EntriesComponent = props =>
  <ListSource
    title={trans('entries_list', {}, 'clacoform')}
    name={selectors.STORE_NAME+'.entries.list'}
    fetch={{
      url: ['apiv2_clacoformentry_list', {clacoForm: props.clacoForm.id}],
      autoload: true
    }}

    source={merge({}, entriesSource(props.clacoForm, props.canViewMetadata, props.canEdit, props.canAdministrate, props.isCategoryManager, props.path, props.currentUser), {
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
                rows.forEach(row => props.downloadEntryPdf(row.id).then(pdfContent => {
                  html2pdf()
                    .set({
                      filename: pdfContent.name,
                      image: { type: 'jpeg', quality: 1 },
                      html2canvas: { scale: 4 }
                    })
                    .from(pdfContent.content, 'string')
                    .save()
                }))
              } else {
                // object
                props.downloadEntryPdf(rows[0].id).then(pdfContent => {
                  html2pdf()
                    .set({
                      filename: pdfContent.name,
                      image: { type: 'jpeg', quality: 1 },
                      html2canvas: { scale: 4 }
                    })
                    .from(pdfContent.content, 'string')
                    .save()
                })
              }
            },
            scope: ['object', 'collection'],
            group: trans('transfer')
          }, {
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            target: `${props.path}/entry/form/${rows[0].id}`,
            displayed: !rows[0].locked && canEditEntry(rows[0], props.clacoForm, props.currentUser),
            scope: ['object'],
            group: trans('management')
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-eye',
            label: trans('publish', {}, 'actions'),
            callback: () => props.switchEntriesStatus(rows, constants.ENTRY_STATUS_PUBLISHED),
            displayed: rows.filter(e => !e.locked && canManageEntry(e, props.canEdit, props.currentUser)).length === rows.length &&
            rows.filter(e => e.status === constants.ENTRY_STATUS_PUBLISHED).length !== rows.length,
            group: trans('management')
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-eye-slash',
            label: trans('unpublish', {}, 'actions'),
            callback: () => props.switchEntriesStatus(rows, constants.ENTRY_STATUS_UNPUBLISHED),
            displayed: rows.filter(e => !e.locked && canManageEntry(e, props.canEdit, props.currentUser)).length === rows.length &&
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
            displayed: rows.filter(e => !e.locked && canManageEntry(e, props.canEdit, props.currentUser)).length === rows.length,
            dangerous: true
          }
        ]
      }
    })}
    parameters={props.listConfiguration}
  />

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
  canAdministrate: T.bool.isRequired,
  canViewMetadata: T.bool.isRequired,
  isCategoryManager: T.bool.isRequired,
  canGeneratePdf: T.bool.isRequired,

  downloadEntryPdf: T.func.isRequired,
  switchEntriesStatus: T.func.isRequired,
  switchEntriesLock: T.func.isRequired,
  deleteEntries: T.func.isRequired,
  downloadFieldValueFile: T.func.isRequired
}

const Entries = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
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
      return dispatch(actions.downloadEntryPdf(entryId))
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
