import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Tool} from '#/main/core/tool'

import {HomeEditor} from '#/plugin/home/tools/home/editor/components/main'
import {flattenTabs, getTabSummary} from '#/plugin/home/tools/home/utils'
import {HomeTab} from '#/plugin/home/tools/home/containers/tab'
import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {HomePageSkeleton} from '#/plugin/home/tools/home/components/page'
import {HomeDashboard} from '#/plugin/home/tools/home/dashboard/components/main'

const HomeTool = props => {
  const tabs = props.tabs
    .filter(tab => props.showHidden || !get(tab, 'restrictions.hidden', false))
    .map((tab) => getTabSummary(props.path, tab))

  return (
    <Tool
      {...props}
      styles={['claroline-distribution-plugin-home-home-tool']}
      menu={1 < tabs.length ? tabs : []}
      editor={HomeEditor}
      redirect={[
        props.loaded && props.tabs[0] && {from: '/', exact: true, to: '/' + props.tabs[0].slug}
      ].filter(redirect => !!redirect)}
      dashboard={HomeDashboard}
      pages={props.loaded ? [
        {
          path: '/:slug',
          onEnter: (params = {}) => props.setCurrentTab(params.slug),
          render: (routeProps) => {
            const flattened = flattenTabs(props.tabs)
            if (flattened.find(tab => tab.slug === routeProps.match.params.slug)) {
              return <HomeTab />
            }

            // tab does not exist
            // let redirection open the first available
            routeProps.history.replace(props.path)

            return null
          }
        }
      ] : undefined}
    >
      {!props.loaded &&
        <HomePageSkeleton />
      }
    </Tool>
  )
}

HomeTool.propTypes = {
  path: T.string.isRequired,
  loaded: T.bool.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  setCurrentTab: T.func.isRequired,
  showHidden: T.bool
}

HomeTool.defaultProps = {
  tabs: []
}

export {
  HomeTool
}
