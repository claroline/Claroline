import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {flattenTabs} from '#/plugin/home/tools/home/utils'
import {PlayerTab} from '#/plugin/home/tools/home/player/components/tab'

const PlayerMain = props =>
  <Routes
    path={props.path}
    redirect={[
      props.tabs[0] && {from: '/', exact: true, to: '/' + props.tabs[0].slug }
    ].filter(redirect => !!redirect)}
    routes={[
      {
        path: '/:slug',
        onEnter: (params = {}) => props.setCurrentTab(params.slug),
        render: (routeProps) => {
          const flattened = flattenTabs(props.tabs)
          if (flattened.find(tab => tab.slug === routeProps.match.params.slug)) {
            const Player = (
              <PlayerTab
                path={props.path}
                currentContext={props.currentContext}
                tabs={props.tabs}
                currentTabTitle={props.currentTabTitle}
                currentTab={props.currentTab}
                loaded={props.loaded}
                accessErrors={props.accessErrors}
                open={props.open}
                dismissRestrictions={props.dismissRestrictions}
                managed={props.managed}
                checkAccessCode={props.checkAccessCode}
              />
            )

            return Player
          }

          // tab does not exist
          // let's redirection open the first available
          routeProps.history.replace(props.path)

          return null
        }
      }
    ]}
  />

PlayerMain.propTypes = {
  path: T.string.isRequired,
  currentContext: T.object.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTabTitle: T.string.isRequired,
  currentTab: T.shape(TabTypes.propTypes),
  loaded: T.bool.isRequired,
  accessErrors: T.object,
  managed: T.bool.isRequired,
  open: T.func.isRequired,
  dismissRestrictions: T.func.isRequired,
  checkAccessCode: T.func.isRequired,
  setCurrentTab: T.func.isRequired
}

export {
  PlayerMain
}
