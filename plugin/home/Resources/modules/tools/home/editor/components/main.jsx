import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
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
          if (props.tabs.find(tab => tab.slug === routeProps.match.params.slug)) {
            const Editor = (
              <EditorTab
                {...props}
                path={props.path}
              />
            )

            return Editor
          }

          // tab does not exist
          // let's redirection open the first available
          routeProps.history.replace(props.path+'/edit')

          return null
        }
      }
    ]}
  />

EditorMain.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  setCurrentTab: T.func.isRequired,
  currentContext: T.object.isRequired,
  administration: T.bool.isRequired,
  readOnly: T.bool.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTabTitle: T.string,
  currentTab: T.shape(TabTypes.propTypes),
  currentTabIndex: T.number.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  createTab: T.func.isRequired,
  deleteTab: T.func.isRequired
}

export {
  EditorMain
}
