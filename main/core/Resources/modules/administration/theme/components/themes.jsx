import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {NavLink, withRouter} from 'react-router-dom'

import {t, trans, transChoice} from '#/main/core/translation'
import {MODAL_CONFIRM, MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {actions as listActions} from '#/main/core/layout/list/actions'
import {actions} from '#/main/core/administration/theme/actions'

import {select as listSelect} from '#/main/core/layout/list/selectors'
import {select} from '#/main/core/administration/theme/selectors'

import {
  PageContainer as Page,
  PageHeader,
  PageContent
} from '#/main/core/layout/page/index'

import {DataList} from '#/main/core/layout/list/components/data-list.jsx'

class Themes extends Component {
  getThemes(themeIds) {
    return themeIds.map(themeId => this.props.themes.find(theme => themeId === theme.id))
  }

  render() {
    return (
      <Page id="theme-management">
        <PageHeader title={t('themes_management')} />
        <PageContent>
          <DataList
            data={this.props.themes}
            totalResults={this.props.themes.length}

            definition={[
              {
                name: 'name',
                type: 'string',
                label: trans('theme_name', {}, 'theme'),
                renderer: (rowData) => [
                  <NavLink key={`link-${rowData.id}`} to={`/${rowData.id}`}>{rowData.name}</NavLink>,
                  rowData.meta.default && <small key={`default-${rowData.id}`}>&nbsp;({trans('default_theme', {}, 'theme')})</small>
                ]
              },
              {name: 'meta.description', type: 'string', label: trans('theme_description', {}, 'theme')},
              {name: 'meta.plugin',      type: 'string', label: trans('theme_plugin', {}, 'theme')},
              {name: 'meta.enabled',     type: 'flag',   label: trans('theme_enabled', {}, 'theme')},
              {name: 'current',          type: 'flag',   label: trans('theme_current', {}, 'theme')}
            ]}

            actions={[
              {
                icon: 'fa fa-fw fa-refresh',
                label: trans('rebuild_theme', {}, 'theme'),
                action: (row) => this.props.rebuildThemes(this.getThemes([row.id]))
              }, {
                icon: 'fa fa-fw fa-trash-o',
                label: trans('remove_theme', {}, 'theme'),
                disabled: (row) => !row.meta.custom,
                action: (row) => this.props.removeThemes(this.getThemes([row.id])),
                isDangerous: true
              }
            ]}

            selection={{
              current: this.props.selected,
              toggle: this.props.toggleSelect,
              toggleAll: this.props.toggleSelectAll,
              actions: [
                {
                  icon: 'fa fa-fw fa-refresh',
                  label: t('rebuild_themes'),
                  action: () => this.props.rebuildThemes(this.getThemes(this.props.selected))
                }, {
                  icon: 'fa fa-fw fa-trash-o',
                  label: t('delete'),
                  action: () => this.props.removeThemes(this.getThemes(this.props.selected)),
                  isDangerous: true
                }
              ]
            }}
          />
        </PageContent>
      </Page>
    )
  }
}

Themes.propTypes = {
  // themes
  themes: T.arrayOf(T.object),

  removeThemes: T.func.isRequired,
  rebuildThemes: T.func.isRequired,

  // list
  selected: T.array.isRequired,
  toggleSelect: T.func.isRequired,
  toggleSelectAll: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    themes: select.themes(state),
    selected: listSelect.selected(state),
    sortBy: listSelect.sortBy(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    rebuildThemes: (themes) => {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: transChoice('rebuild_themes', themes.length, {count: themes.length}, 'theme'),
          question: trans('rebuild_themes_confirm', {
            theme_list: themes.map(theme => theme.name).join(', ')
          }, 'theme'),
          handleConfirm: () => dispatch(actions.rebuildThemes(themes))
        })
      )
    },

    removeThemes: (themes) => {
      dispatch(
        modalActions.showModal(MODAL_DELETE_CONFIRM, {
          title: transChoice('remove_themes', themes.length, {count: themes.length}, 'theme'),
          question: trans('remove_themes_confirm', {
            theme_list: themes.map(theme => theme.name).join(', ')
          }, 'theme'),
          handleConfirm: () => dispatch(actions.deleteThemes(themes))
        })
      )
    },

    // selection
    toggleSelect: (id) => dispatch(listActions.toggleSelect(id)),
    toggleSelectAll: (items) => dispatch(listActions.toggleSelectAll(items))
  }
}

const ConnectedThemes = withRouter(connect(mapStateToProps, mapDispatchToProps)(Themes))

export {
  ConnectedThemes as Themes
}
