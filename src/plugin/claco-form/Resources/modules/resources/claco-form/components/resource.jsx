import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Resource} from '#/main/core/resource'

import {ClacoForm as ClacoFormTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {Overview} from '#/plugin/claco-form/resources/claco-form/components/overview'
import {Entries} from '#/plugin/claco-form/resources/claco-form/player/components/entries'
import {EntryForm} from '#/plugin/claco-form/resources/claco-form/player/components/entry-form'
import {Entry} from '#/plugin/claco-form/resources/claco-form/player/components/entry'
import {StatsMain} from '#/plugin/claco-form/resources/claco-form/stats/containers/main'
import {ClacoFormEditor} from '#/plugin/claco-form/resources/claco-form/editor/containers/main'


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
  <Resource
    {...omit(props)}
    styles={['claroline-distribution-plugin-claco-form-resource']}
    menu={[
      {
        name: 'list',
        type: LINK_BUTTON,
        /*icon: 'fa fa-fw fa-search',*/
        label: trans('entries_list', {}, 'clacoform'),
        displayed: props.canSearchEntry,
        target: `${props.path}/entries`,
        exact: true
      }, {
        name: 'random',
        type: LINK_BUTTON,
        label: trans('random_entry', {}, 'clacoform'),
        target: `${props.path}/random`,
        displayed: props.randomEnabled
      }
    ]}
    actions={[
      {
        name: 'statistics',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pie-chart',
        label: trans('show-statistics', {}, 'actions'),
        target: `${props.path}/stats`,
        displayed: props.canEdit && props.hasStatistics
      }, {
        name: 'export-entries',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export_all_entries', {}, 'clacoform'),
        displayed: props.canAdministrate,
        target: ['claro_claco_form_entries_export', {clacoForm: props.clacoForm.id}],
        group: trans('transfer')
      }
    ]}
    editor={ClacoFormEditor}
    overviewPage={Overview}
    pages={[
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
              fetch(url(['apiv2_clacoformentry_random', {clacoForm: props.clacoForm.id}]), {
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
        path: '/random',
        disabled: !props.randomEnabled,
        onEnter: () => {
          fetch(url(['apiv2_clacoformentry_random', {clacoForm: props.clacoForm.id}]), {
            method: 'GET' ,
            credentials: 'include'
          })
            .then(response => response.json())
            .then(entryId => {
              props.history.push(`${props.path}/entries/${entryId}`)
            })
        }
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
      }, {
        path: '/stats',
        disabled: !props.canEdit,
        onEnter: () => props.loadStats(props.clacoForm.id),
        component: StatsMain
      }
    ]}
  />

ClacoFormResource.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  currentUser: T.object,
  clacoForm: T.shape(
    ClacoFormTypes.propTypes
  ).isRequired,
  hasStatistics: T.bool,
  randomEnabled: T.bool,
  canEdit: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  canSearchEntry: T.bool.isRequired,
  defaultHome: T.string,
  resetForm: T.func.isRequired,
  openEntryForm: T.func.isRequired,
  loadEntryUser: T.func.isRequired,
  loadAllUsedCountries: T.func.isRequired,
  loadStats: T.func.isRequired
}

export {
  ClacoFormResource
}
