import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {currentUser} from '#/main/core/user/current'
import {makeId} from '#/main/core/scaffolding/id'
import {now} from '#/main/core/scaffolding/date'
import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'
import {RoutedPageContent} from '#/main/core/layout/router'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'
import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'

import {actions as entryActions} from '#/plugin/claco-form/resources/claco-form/player/entry/actions'
import {select} from '#/plugin/claco-form/resources/claco-form/selectors'
import {ClacoForm as ClacoFormType} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {ClacoFormMainMenu} from '#/plugin/claco-form/resources/claco-form/player/components/claco-form-main-menu.jsx'
import {Editor} from '#/plugin/claco-form/resources/claco-form/editor/components/editor.jsx'
import {TemplateForm} from '#/plugin/claco-form/resources/claco-form/editor/template/components/template-form.jsx'
import {Entries} from '#/plugin/claco-form/resources/claco-form/player/entry/components/entries.jsx'
import {EntryForm} from '#/plugin/claco-form/resources/claco-form/player/entry/components/entry-form.jsx'
import {Entry} from '#/plugin/claco-form/resources/claco-form/player/entry/components/entry.jsx'

const authenticatedUser = currentUser()

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

const Resource = props =>
  <ResourcePageContainer
    editor={{
      path: '/edit',
      save: {
        disabled: !props.saveEnabled,
        action: () => props.saveForm(props.clacoForm.id)
      }
    }}
    customActions={[
      {
        type: 'link',
        icon: 'fa fa-fw fa-home',
        label: trans('main_menu', {}, 'clacoform'),
        target: '/menu'
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-plus',
        label: trans('add_entry', {}, 'clacoform'),
        displayed: props.canAddEntry,
        target: '/entry/form',
        exact: true
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-search',
        label: trans('entries_list', {}, 'clacoform'),
        displayed: props.canSearchEntry,
        target: '/entries',
        exact: true
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-file-text-o',
        label: trans('template_management', {}, 'clacoform'),
        displayed: props.canEdit,
        target: '/template'
      }, {
        type: 'url',
        icon: 'fa fa-fw fa-upload',
        label: trans('export_all_entries', {}, 'clacoform'),
        displayed: props.canEdit,
        target: ['claro_claco_form_entries_export', {clacoForm: props.clacoForm.id}]
      }
    ]}
  >
    <RoutedPageContent
      headerSpacer={false}
      redirect={[]}
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
  </ResourcePageContainer>

Resource.propTypes = {
  clacoForm: T.shape(ClacoFormType.propTypes).isRequired,
  canEdit: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  canSearchEntry: T.bool.isRequired,
  defaultHome: T.string.isRequired,
  saveEnabled: T.bool.isRequired,
  resetForm: T.func.isRequired,
  saveForm: T.func.isRequired,
  openEntryForm: T.func.isRequired,
  resetEntryForm: T.func.isRequired,
  loadEntryUser: T.func.isRequired,
  resetEntryUser: T.func.isRequired,
  loadAllUsedCountries: T.func.isRequired
}

const ClacoFormResource = connect(
  (state) => ({
    clacoForm: select.clacoForm(state),
    canEdit: select.canAdministrate(state),
    canAddEntry: select.canAddEntry(state),
    canSearchEntry: select.canSearchEntry(state),
    defaultHome: select.getParam(state, 'default_home'),
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'clacoFormForm'))
  }),
  (dispatch) => ({
    resetForm(formData) {
      dispatch(formActions.resetForm('clacoFormForm', formData))
    },
    saveForm(id) {
      dispatch(formActions.saveForm('clacoFormForm', ['apiv2_clacoform_update', {id: id}]))
    },
    openEntryForm(id, clacoFormId, fields = []) {
      const defaultValue = {
        id: makeId(),
        values: {},
        clacoForm: {
          id: clacoFormId
        },
        user: authenticatedUser,
        categories: [],
        keywords: []
      }
      fields.forEach(f => {
        if (f.type === 'date') {
          defaultValue.values[f.id] = now()
        }
      })

      dispatch(entryActions.openForm('entries.current', id, defaultValue))
    },
    resetEntryForm() {
      dispatch(formActions.resetForm('entries.current', {}, true))
    },
    loadEntryUser(entryId) {
      dispatch(entryActions.loadEntryUser(entryId))
    },
    resetEntryUser() {
      dispatch(entryActions.resetEntryUser())
    },
    loadAllUsedCountries(clacoFormId) {
      dispatch(entryActions.loadAllUsedCountries(clacoFormId))
    }
  })
)(Resource)

export {
  ClacoFormResource
}
