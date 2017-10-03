import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {Route, Switch, withRouter} from 'react-router-dom'
import {ResourceContainer} from '#/main/core/layout/resource/containers/resource.jsx'
import {trans} from '#/main/core/translation'
import {actions as editorActions} from '../editor/actions'
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
import {selectors} from '../selectors'

const ClacoFormResource = props =>
  <ResourceContainer
    editor={{
      opened: '/edit' === props.location.pathname,
      open: '#/edit',
      save: {
        disabled: false,
        action: props.saveParameters
      }
    }}
    customActions={customActions(props)}
  >
    <Switch>
      <Route
        path="/"
        component={
          props.defaultHome === 'menu' ?
            ClacoFormMainMenu :
            props.defaultHome === 'search' ?
              Entries :
              props.defaultHome === 'add' ?
                EntryCreateForm :
                props.defaultHome === 'random' ?
                  EntryView :
                  ClacoFormMainMenu
        }
        exact={true} />
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
  </ResourceContainer>

ClacoFormResource.propTypes = {
  location: T.shape({
    pathname: T.string.isRequired
  }).isRequired,
  saveParameters: T.func.isRequired,
  canEdit: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  canSearchEntry: T.bool.isRequired,
  defaultHome: T.string.isRequired
}

function customActions(props) {
  const actions = []

  actions.push({
    icon: 'fa fa-fw fa-home',
    label: trans('main_menu', {}, 'clacoform'),
    action: '#/menu'
  })
  if (props.canAddEntry) {
    actions.push({
      icon: 'fa fa-fw fa-edit',
      label: trans('add_entry', {}, 'clacoform'),
      action: '#/entry/create'
    })
  }
  if (props.canSearchEntry) {
    actions.push({
      icon: 'fa fa-fw fa-search',
      label: trans('entries_list', {}, 'clacoform'),
      action: '#/entries'
    })
  }
  if (props.canEdit) {
    actions.push({
      icon: 'fa fa-fw fa-th-list',
      label: trans('fields_management', {}, 'clacoform'),
      action: '#/fields'
    })
    actions.push({
      icon: 'fa fa-fw fa-file-text-o',
      label: trans('template_management', {}, 'clacoform'),
      action: '#/template'
    })
    actions.push({
      icon: 'fa fa-fw fa-table',
      label: trans('categories_management', {}, 'clacoform'),
      action: '#/categories'
    })
    actions.push({
      icon: 'fa fa-fw fa-font',
      label: trans('keywords_management', {}, 'clacoform'),
      action: '#/keywords'
    })
  }

  return actions
}

function mapStateToProps(state) {
  return {
    canEdit: state.canEdit,
    canAddEntry: selectors.canAddEntry(state),
    canSearchEntry: selectors.canSearchEntry(state),
    defaultHome: selectors.getParam(state, 'default_home')
  }
}

function mapDispatchToProps(dispatch) {
  return {
    saveParameters: () => dispatch(editorActions.saveParameters())
  }
}

const ConnectedClacoFormResource = withRouter(connect(mapStateToProps, mapDispatchToProps)(ClacoFormResource))

export {ConnectedClacoFormResource as ClacoFormResource}
