import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {PageSimple} from '#/main/app/page/components/simple'
import {PageHeader, PageContent, PageActions, PageAction} from '#/main/core/layout/page'
import {LINK_BUTTON} from '#/main/app/buttons'
import {getToolPath, showToolBreadcrumb} from '#/main/core/tool/utils'

import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {WidgetGrid} from '#/main/core/widget/player/components/grid'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {selectors} from '#/main/core/tools/home/store'
import {selectors as playerSelectors} from '#/main/core/tools/home/player/store'
import {Tabs} from '#/main/core/tools/home/components/tabs'

const PlayerComponent = props =>
  <PageSimple
    className="home-tool"
    showBreadCrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
    path={[].concat(getToolPath('home', props.currentContext.type, props.currentContext.data), props.currentTab ? [{
      label: props.currentTab.title,
      target: '/' // this don't work but it's never used as current tab is always last for now
    }] : [])}
  >
    <PageHeader
      className={props.currentTab && props.currentTab.centerTitle ? 'text-center' : ''}
      title={props.currentTabTitle}
      poster={props.currentTab && props.currentTab.poster ? props.currentTab.poster.url: undefined}
    >
      {1 < props.tabs.length &&
        <Tabs
          tabs={props.tabs}
          currentContext={props.currentContext}
          editing={false}
        />
      }

      {(props.currentTab && props.editable) &&
        <PageActions>
          <PageAction
            type={LINK_BUTTON}
            label={trans('configure', {}, 'actions')}
            icon="fa fa-fw fa-cog"
            target={`/edit/tab/${props.currentTab.id}`}
            primary={true}
          />
        </PageActions>
      }
    </PageHeader>

    <PageContent>
      <WidgetGrid
        currentContext={props.currentContext}
        widgets={props.widgets}
      />
    </PageContent>
  </PageSimple>

PlayerComponent.propTypes = {
  currentContext: T.object.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTabTitle: T.string.isRequired,
  currentTab: T.shape(TabTypes.propTypes),
  editable: T.bool.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  )).isRequired
}

const Player = connect(
  (state) => ({
    currentContext: selectors.context(state),
    editable: selectors.editable(state),
    tabs: playerSelectors.tabs(state),
    currentTab: selectors.currentTab(state),
    currentTabTitle: selectors.currentTabTitle(state),
    widgets: selectors.widgets(state)
  })
)(PlayerComponent)

export {
  Player
}
