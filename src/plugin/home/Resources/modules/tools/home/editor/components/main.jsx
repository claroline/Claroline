import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {flattenTabs} from '#/plugin/home/tools/home/utils'
import {EditorTab} from '#/plugin/home/tools/home/editor/components/tab'

const EditorMain = props =>
  <Routes
    path={props.path}
    redirect={[
      props.tabs[0] && {from: '/edit', exact: true, to: '/edit/'+props.tabs[0].slug }
    ].filter(redirect => !!redirect)}
    routes={[
      {
        path: '/edit/:slug',
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
          routeProps.history.replace(props.path+'/edit')

          return null
        }
      }
    ]}
  />

EditorMain.propTypes = {
  path: T.string.isRequired,
  setCurrentTab: T.func.isRequired,
  currentContext: T.object.isRequired,
  tabs: T.arrayOf(T.shape(
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
  EditorMain
}
