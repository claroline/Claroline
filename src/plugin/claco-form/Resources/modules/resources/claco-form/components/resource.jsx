import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {ClacoForm as ClacoFormType} from '#/plugin/claco-form/resources/claco-form/prop-types'

import {Overview} from '#/plugin/claco-form/resources/claco-form/overview/components/overview'
import {EditorMain} from '#/plugin/claco-form/resources/claco-form/editor/containers/main'
import {Entries} from '#/plugin/claco-form/resources/claco-form/player/components/entries'
import {EntryForm} from '#/plugin/claco-form/resources/claco-form/player/components/entry-form'
import {Entry} from '#/plugin/claco-form/resources/claco-form/player/components/entry'

function getHome(type) {
  switch (type) {
    case 'search':
      return Entries

    case 'add':
      return EntryForm

    case 'random':
      return Entry

    case 'menu':
    default:
      return Overview
  }
}

const ClacoFormResource = props =>
  <ResourcePage
    primaryAction="add-entry"
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        target: `${props.path}/menu`
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-search',
        label: trans('entries_list', {}, 'clacoform'),
        displayed: props.canSearchEntry,
        target: `${props.path}/entries`,
        exact: true
      }, {
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export_all_entries', {}, 'clacoform'),
        displayed: props.canEdit,
        target: ['claro_claco_form_entries_export', {clacoForm: props.clacoForm.id}],
        group: trans('transfer')
      }
    ]}
    routes={[
      {
        path: '/',
        component: getHome(props.defaultHome),
        exact: true,
        onEnter: () => {
          switch (props.defaultHome) {
            case 'search':
              props.loadAllUsedCountries(props.clacoForm.id)
              break
            case 'add':
              props.openEntryForm(null, props.clacoForm.id, [], props.currentUser)
              break
            case 'random':
              fetch(url(['claro_claco_form_entry_random', {clacoForm: props.clacoForm.id}]), {
                method: 'GET' ,
                credentials: 'include'
              })
                .then(response => response.json())
                .then(entryId => {
                  if (entryId) {
                    props.openEntryForm(entryId, props.clacoForm.id, [], props.currentUser)
                    props.loadEntryUser(entryId, props.currentUser)
                  }
                })
              break
          }
        }
      }, {
        path: '/menu',
        component: Overview
      }, {
        path: '/edit',
        component: EditorMain,
        disabled: !props.canEdit,
        onLeave: () => props.resetForm(),
        onEnter: () => props.resetForm(props.clacoForm)
      }, {
        path: '/entries',
        component: Entries,
        exact: true,
        disabled: !props.canSearchEntry,
        onEnter: () => props.loadAllUsedCountries(props.clacoForm.id)
      }, {
        path: '/entries/:id',
        component: Entry,
        onEnter: (params) => {
          props.openEntryForm(params.id, props.clacoForm.id, [], props.currentUser)
          props.loadEntryUser(params.id, props.currentUser)
        }
      }, {
        path: '/entry/form/:id?',
        component: EntryForm,
        onEnter: (params) => {
          props.openEntryForm(params.id, props.clacoForm.id, props.clacoForm.fields, props.currentUser)

          if (params.id) {
            props.loadEntryUser(params.id, props.currentUser)
          }
        }
      }
    ]}
  />

ClacoFormResource.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  clacoForm: T.shape(ClacoFormType.propTypes).isRequired,
  canEdit: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  canSearchEntry: T.bool.isRequired,
  defaultHome: T.string,
  resetForm: T.func.isRequired,
  openEntryForm: T.func.isRequired,
  loadEntryUser: T.func.isRequired,
  loadAllUsedCountries: T.func.isRequired
}

export {
  ClacoFormResource
}
