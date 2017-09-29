import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {NavLink, withRouter} from 'react-router-dom'

import {t, trans, transChoice} from '#/main/core/translation'
import {MODAL_CONFIRM, MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {actions} from '#/main/core/administration/theme/actions'

import {
  PageContainer as Page,
  PageHeader,
  PageContent
} from '#/main/core/layout/page'

import {DataListContainer as DataList} from '#/main/core/layout/list/containers/data-list.jsx'

const ThemesPage = props =>
  <Page id="theme-management">
    <PageHeader title={t('themes_management')} />
    <PageContent>
      <DataList
        name="themes"

        definition={[
          {
            name: 'name',
            label: trans('theme_name', {}, 'theme'),
            renderer: (rowData) => [
              <NavLink key={`link-${rowData.id}`} to={`/${rowData.id}`}>{rowData.name}</NavLink>,
              rowData.meta.default && <small key={`default-${rowData.id}`}>&nbsp;({trans('default_theme', {}, 'theme')})</small>
            ],
            displayed: true
          },
          {name: 'meta.description', label: trans('theme_description', {}, 'theme'), displayed: true},
          {name: 'meta.plugin',      label: trans('theme_plugin', {}, 'theme'), displayed: true},
          {name: 'meta.enabled',     type: 'boolean',   label: trans('theme_enabled', {}, 'theme'), displayed: true},
          {name: 'current',          type: 'boolean',   label: trans('theme_current', {}, 'theme'), displayed: true}
        ]}

        actions={[
          {
            icon: 'fa fa-fw fa-refresh',
            label: trans('rebuild_theme', {}, 'theme'),
            action: (rows) => props.rebuildThemes(rows)
          }, {
            icon: 'fa fa-fw fa-trash-o',
            label: t('delete'),
            disabled: (rows) => !rows.find(row => row.meta.custom), // at least one theme should be deletable
            action: (rows) => props.removeThemes(rows),
            isDangerous: true
          }
        ]}

        card={(row) => ({
          icon: 'fa fa-paint-brush',
          title: row.name,
          subtitle: row.meta.plugin || (row.meta.creator ? row.meta.creator.name : t('unknown')),
          contentText: row.meta.description,
          flags: [
            row.current      && ['fa fa-check', t('theme_current')],
            row.meta.enabled && ['fa fa-eye',   t('theme_enabled')]
          ].filter(flag => !!flag)
        })}
      />
    </PageContent>
  </Page>

ThemesPage.propTypes = {
  rebuildThemes: T.func.isRequired,
  removeThemes: T.func.isRequired
}

function mapDispatchToProps(dispatch) {
  return {
    rebuildThemes(themes) {
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

    removeThemes(themes) {
      dispatch(
        modalActions.showModal(MODAL_DELETE_CONFIRM, {
          title: transChoice('remove_themes', themes.length, {count: themes.length}, 'theme'),
          question: trans('remove_themes_confirm', {
            theme_list: themes.map(theme => theme.name).join(', ')
          }, 'theme'),
          handleConfirm: () => dispatch(actions.deleteThemes(themes))
        })
      )
    }
  }
}

const Themes = withRouter(connect(null, mapDispatchToProps)(ThemesPage))

export {
  Themes
}
