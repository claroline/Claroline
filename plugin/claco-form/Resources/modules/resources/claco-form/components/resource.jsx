import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {ClacoForm as ClacoFormType} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {ClacoFormMainMenu} from '#/plugin/claco-form/resources/claco-form/player/components/claco-form-main-menu'
import {Editor} from '#/plugin/claco-form/resources/claco-form/editor/components/editor'
import {TemplateForm} from '#/plugin/claco-form/resources/claco-form/editor/template/components/template-form'
import {Entries} from '#/plugin/claco-form/resources/claco-form/player/entry/components/entries'
import {EntryForm} from '#/plugin/claco-form/resources/claco-form/player/entry/components/entry-form'
import {Entry} from '#/plugin/claco-form/resources/claco-form/player/entry/components/entry'

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
      return ClacoFormMainMenu
  }
}

const ClacoFormResource = props =>
  <ResourcePage
    styles={['claroline-distribution-plugin-claco-form-resource']}
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('main_menu', {}, 'clacoform'),
        target: '/menu'
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_entry', {}, 'clacoform'),
        displayed: props.canAddEntry,
        target: '/entry/form',
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-search',
        label: trans('entries_list', {}, 'clacoform'),
        displayed: props.canSearchEntry,
        target: '/entries',
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-file-text-o',
        label: trans('template_management', {}, 'clacoform'),
        displayed: props.canEdit,
        target: '/template'
      }, {
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-upload',
        label: trans('export_all_entries', {}, 'clacoform'),
        displayed: props.canEdit,
        target: ['claro_claco_form_entries_export', {clacoForm: props.clacoForm.id}]
      }
    ]}
  >
    <Routes
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
                props.openEntryForm(null, props.clacoForm.id)
                break
              case 'random':
                fetch(url(['claro_claco_form_entry_random', {clacoForm: props.clacoForm.id}]), {
                  method: 'GET' ,
                  credentials: 'include'
                })
                  .then(response => response.json())
                  .then(entryId => {
                    if (entryId) {
                      props.openEntryForm(entryId, props.clacoForm.id)
                      props.loadEntryUser(entryId)
                    }
                  })
                break
            }
          }
        }, {
          path: '/menu',
          component: ClacoFormMainMenu
        }, {
          path: '/edit',
          component: Editor,
          disabled: !props.canEdit,
          onLeave: () => props.resetForm(),
          onEnter: () => props.resetForm(props.clacoForm)
        }, {
          path: '/template',
          component: TemplateForm,
          disabled: !props.canEdit
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
            props.openEntryForm(params.id, props.clacoForm.id)
            props.loadEntryUser(params.id)
          },
          onLeave: () => {
            props.resetEntryForm()
            props.resetEntryUser()
          }
        }, {
          path: '/entry/form/:id?',
          component: EntryForm,
          onEnter: (params) => {
            props.openEntryForm(params.id, props.clacoForm.id, props.clacoForm.fields)

            if (params.id) {
              props.loadEntryUser(params.id)
            }
          },
          onLeave: () => {
            props.resetEntryForm()
            props.resetEntryUser()
          }
        }
      ]}
    />
  </ResourcePage>

ClacoFormResource.propTypes = {
  clacoForm: T.shape(ClacoFormType.propTypes).isRequired,
  canEdit: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  canSearchEntry: T.bool.isRequired,
  defaultHome: T.string.isRequired,
  resetForm: T.func.isRequired,
  openEntryForm: T.func.isRequired,
  resetEntryForm: T.func.isRequired,
  loadEntryUser: T.func.isRequired,
  resetEntryUser: T.func.isRequired,
  loadAllUsedCountries: T.func.isRequired
}

export {
  ClacoFormResource
}
