import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {ToolEditor} from '#/main/core/tool/editor/containers/main'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {flattenTabs, getTabSummary} from '#/plugin/home/tools/home/utils'

import {EditorTab} from '#/plugin/home/tools/home/editor/components/tab'
import {MODAL_HOME_CREATION} from '#/plugin/home/tools/home/editor/modals/creation'

const HomeEditor = props => {
  useEffect(() => {
    if (props.loaded) {
      // load tool parameters inside the form
      props.load(props.tabs)
    }
  }, [props.contextType, props.contextId, props.loaded])

  const tabs = props.editorTabs.map((tab) => getTabSummary(`${props.path}/edit`, tab, true))

  return (
    <ToolEditor
      styles={['claroline-distribution-plugin-home-home-tool']}
      menu={(1 < tabs.length ? tabs : []).concat([
        {
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add_tab', {}, 'home'),
          modal: [MODAL_HOME_CREATION, {
            position: props.editorTabs.length,
            create: (tab) => {
              props.createTab(null, tab, (slug) => props.history.push(`${props.path}/edit/${slug}`))
            }
          }]
        }
      ])}
      pages={[
        {
          path: '/:slug',
          onEnter: (params = {}) => props.setCurrentTab(params.slug),
          render: (routeProps) => {
            const flattened = flattenTabs(props.tabs)
            if (flattened.find(tab => tab.slug === routeProps.match.params.slug)) {
              const Editor = (
                <EditorTab
                  {...props}
                  path={props.path}
                />
              )

              return Editor
            }

            // tab does not exist, let redirection open the first available
            routeProps.history.replace(props.path + '/edit')

            return null
          }
        }
      ]}
      redirect={[
        props.tabs[0] && {from: '/edit', exact: true, to: '/edit/' + props.tabs[0].slug}
      ].filter(redirect => !!redirect)}
    />
  )
}
HomeEditor.propTypes = {
  path: T.string.isRequired,
  setCurrentTab: T.func.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  editorTabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTabTitle: T.string,
  currentTab: T.shape(TabTypes.propTypes),
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  createTab: T.func.isRequired,
  deleteTab: T.func.isRequired
}

export {
  HomeEditor
}
