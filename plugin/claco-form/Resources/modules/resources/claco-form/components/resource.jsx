import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {Route, Switch, withRouter} from 'react-router-dom'

import {trans} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'

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
      opened: '/edit' === props.location.pathname,
      open: '#/edit',
      save: {
        disabled: false,
        action: props.saveParameters
      }
    }}
    customActions={[
      {
        icon: 'fa fa-fw fa-home',
        label: trans('main_menu', {}, 'clacoform'),
        action: '#/menu'
      }, {
        icon: 'fa fa-fw fa-plus',
        label: trans('add_entry', {}, 'clacoform'),
        displayed: props.canAddEntry,
        action: '#/entry/create'
      }, {
        icon: 'fa fa-fw fa-search',
        label: trans('entries_list', {}, 'clacoform'),
        displayed: props.canSearchEntry,
        action: '#/entries'
      }, {
        icon: 'fa fa-fw fa-th-list',
        label: trans('fields_management', {}, 'clacoform'),
        displayed: props.canEdit,
        action: '#/fields'
      }, {
        icon: 'fa fa-fw fa-file-text-o',
        label: trans('template_management', {}, 'clacoform'),
        displayed: props.canEdit,
        action: '#/template'
      }, {
        icon: 'fa fa-fw fa-table',
        label: trans('categories_management', {}, 'clacoform'),
        displayed: props.canEdit,
        action: '#/categories'
      }, {
        icon: 'fa fa-fw fa-font',
        label: trans('keywords_management', {}, 'clacoform'),
        displayed: props.canEdit,
        action: '#/keywords'
      }, {
        icon: 'fa fa-fw fa-upload',
        label: trans('export_all_entries', {}, 'clacoform'),
        displayed: props.canEdit,
        action: generateUrl('claro_claco_form_entries_export', {clacoForm: props.resource.id})
      }, {
        icon: 'fa fa-fw fa-trash-o',
        label: trans('delete_all_entries', {}, 'clacoform'),
        displayed: props.canEdit,
        action: props.deleteEntries,
        dangerous: true
      }
    ]}
  >
    <Switch>
      <Route path="/" component={getHome(props.defaultHome)} exact={true} />
      <Route path="/menu" component={ClacoFormMainMenu} exact={true} />
      <Route path="/edit" component={ClacoFormConfig} />
      <Route path="/categories" component={Categories} />
      <Route path="/keywords" component={Keywords} />
      <Route path="/fields" component={Fields} />
      <Route path="/template" component={TemplateForm} />
      <Route path="/entries" component={Entries} />
      <Route path="/entry/create" component={EntryCreateForm} />
      <Route path="/entry/:id/edit" component={EntryEditForm} />
      <Route path="/entry/:id/view" component={EntryView} />
    </Switch>
  </ResourcePageContainer>

Resource.propTypes = {
  location: T.shape({
    pathname: T.string.isRequired
  }).isRequired,
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

const ConnectedClacoFormResource = withRouter(connect(mapStateToProps, mapDispatchToProps)(Resource))

export {
  ConnectedClacoFormResource as ClacoFormResource
}
