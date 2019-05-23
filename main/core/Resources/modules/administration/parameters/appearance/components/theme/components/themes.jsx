import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {withRouter} from 'react-router-dom'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {
  PageContainer,
  PageHeader,
  PageContent
} from '#/main/core/layout/page'
import {DataCard} from '#/main/app/content/card/components/data'
import {ListData} from '#/main/app/content/list/containers/data'

import {actions} from '#/main/core/administration/parameters/appearance/components/theme/actions'

const ThemesPage = props =>
  <PageContainer id="theme-management">
    <PageHeader title={trans('themes_management')} />

    <PageContent>
      <ListData
        name="themes"
        fetch={{
          url: ['apiv2_theme_list'],
          autoload: true
        }}
        definition={[
          {
            name: 'name',
            type: 'string',
            label: trans('theme_name', {}, 'theme'),
            primary: true,
            displayed: true
          },
          {name: 'meta.description', label: trans('theme_description', {}, 'theme'), displayed: true},
          {name: 'meta.plugin',      label: trans('theme_plugin', {}, 'theme'), displayed: true},
          {name: 'meta.enabled',     type: 'boolean',   label: trans('theme_enabled', {}, 'theme'), displayed: true},
          {name: 'meta.default',     type: 'boolean',   label: trans('default_theme', {}, 'theme'), displayed: true},
          {name: 'current',          type: 'boolean',   label: trans('theme_current', {}, 'theme'), displayed: true}
        ]}

        primaryAction={(row) => ({
          type: LINK_BUTTON,
          target: `/${row.id}`
        })}
        actions={(rows) => [
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-refresh',
            label: trans('rebuild_theme', {}, 'theme'),
            confirm: {
              title: transChoice('rebuild_themes', rows.length, {count: rows.length}, 'theme'),
              message: trans('rebuild_themes_confirm', {
                theme_list: rows.map(theme => theme.name).join(', ')
              }, 'theme')
            },
            callback: () => props.rebuildThemes(rows)
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash-o',
            label: trans('delete', {}, 'actions'),
            disabled: !rows.find(row => row.meta.custom), // at least one theme should be deletable
            confirm: {
              title: transChoice('remove_themes', rows.length, {count: rows.length}, 'theme'),
              message: trans('remove_themes_confirm', {
                theme_list: rows.map(theme => theme.name).join(', ')
              }, 'theme')
            },
            callback: () => props.removeThemes(rows),
            dangerous: true
          }
        ]}

        card={(row) =>
          <DataCard
            icon='fa fa-paint-brush'
            title={row.data.name}
            subtitle={row.data.meta.plugin || (row.data.meta.creator ? row.data.meta.creator.name : trans('unknown'))}
            contentText={row.data.meta.description}
            flags={[
              row.data.current      && ['fa fa-check', trans('theme_current')],
              row.data.meta.enabled && ['fa fa-eye',   trans('theme_enabled')]
            ].filter(flag => !!flag)}
          />
        }
      />
    </PageContent>
  </PageContainer>

ThemesPage.propTypes = {
  rebuildThemes: T.func.isRequired,
  removeThemes: T.func.isRequired
}

function mapDispatchToProps(dispatch) {
  return {
    rebuildThemes(themes) {
      dispatch(actions.rebuildThemes(themes))
    },

    removeThemes(themes) {
      dispatch(actions.deleteThemes(themes))
    }
  }
}

const Themes = withRouter(connect(null, mapDispatchToProps)(ThemesPage))

export {
  Themes
}
