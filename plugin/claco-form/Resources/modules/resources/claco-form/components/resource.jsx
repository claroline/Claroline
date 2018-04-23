import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'

import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {select as resourceSelect} from '#/main/core/resource/selectors'

import {actions as editorActions} from '../editor/actions'
import {selectors} from '../selectors'

import {ClacoFormMainMenu} from '../player/components/claco-form-main-menu.jsx'
import {ClacoFormConfig} from '../editor/components/claco-form-config.jsx'
import {Categories} from '../editor/category/components/categories.jsx'
import {Keywords} from '../editor/keyword/components/keywords.jsx'
import {Fields} from '../editor/field/components/fields.jsx'
import {TemplateForm} from '../editor/template/components/template-form.jsx'
import {Entries} from '../player/entry/components/entries.jsx'
import {EntryCreateForm} from '../player/entry/components/entry-create-form.jsx'
import {EntryEditForm} from '../player/entry/components/entry-edit-form.jsx'
import {EntryView} from '../player/entry/components/entry-view.jsx'

function getHome(type) {
  switch (type) {
    case 'search':
      return Entries

    case 'add':
      return EntryCreateForm

    case 'random':
      return EntryView

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
        disabled: false,
        action: props.saveParameters
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
        target: '/entry/create'
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-search',
        label: trans('entries_list', {}, 'clacoform'),
        displayed: props.canSearchEntry,
        target: '/entries'
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-th-list',
        label: trans('fields_management', {}, 'clacoform'),
        displayed: props.canEdit,
        target: '/fields'
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-file-text-o',
        label: trans('template_management', {}, 'clacoform'),
        displayed: props.canEdit,
        target: '/template'
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-table',
        label: trans('categories_management', {}, 'clacoform'),
        displayed: props.canEdit,
        target: '/categories'
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-font',
        label: trans('keywords_management', {}, 'clacoform'),
        displayed: props.canEdit,
        target: '/keywords'
      }, {
        type: 'url',
        icon: 'fa fa-fw fa-upload',
        label: trans('export_all_entries', {}, 'clacoform'),
        displayed: props.canEdit,
        target: ['claro_claco_form_entries_export', {clacoForm: props.resource.id}]
      }, {
        type: 'callback',
        icon: 'fa fa-fw fa-trash-o',
        label: trans('delete_all_entries', {}, 'clacoform'),
        displayed: props.canEdit,
        callback: props.deleteEntries,
        dangerous: true
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
          exact: true
        }, {
          path: '/menu',
          component: ClacoFormMainMenu
        }, {
          path: '/edit',
          component: ClacoFormConfig
        }, {
          path: '/categories',
          component: Categories
        }, {
          path: '/keywords',
          component: Keywords
        }, {
          path: '/fields',
          component: Fields
        }, {
          path: '/template',
          component: TemplateForm
        }, {
          path: '/entries',
          component: Entries
        }, {
          path: '/entry/create',
          component: EntryCreateForm
        }, {
          path: '/entry/:id/edit',
          component: EntryEditForm
        }, {
          path: '/entry/:id/view',
          component: EntryView
        }
      ]}
    />
  </ResourcePageContainer>

Resource.propTypes = {
  resource: T.shape({
    id: T.number.isRequired
  }).isRequired,
  saveParameters: T.func.isRequired,
  deleteEntries: T.func.isRequired,
  canEdit: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  canSearchEntry: T.bool.isRequired,
  defaultHome: T.string.isRequired
}

function mapStateToProps(state) {
  return {
    resource: selectors.resource(state),
    canEdit: resourceSelect.editable(state),
    canAddEntry: selectors.canAddEntry(state),
    canSearchEntry: selectors.canSearchEntry(state),
    defaultHome: selectors.getParam(state, 'default_home')
  }
}

function mapDispatchToProps(dispatch) {
  return {
    saveParameters: () => dispatch(editorActions.saveParameters()),
    deleteEntries: () => {
      dispatch(
        modalActions.showModal(MODAL_DELETE_CONFIRM, {
          title: trans('delete_all_entries', {}, 'clacoform'),
          question: trans('delete_all_entries_confirm', {}, 'clacoform'),
          handleConfirm: () => dispatch(editorActions.deleteAllEntries())
        })
      )

    }
  }
}

const ConnectedClacoFormResource = connect(mapStateToProps, mapDispatchToProps)(Resource)

export {
  ConnectedClacoFormResource as ClacoFormResource
}
