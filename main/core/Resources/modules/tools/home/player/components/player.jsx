import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {WidgetGrid} from '#/main/core/widget/player/components/grid'
import {trans} from '#/main/core/translation'
import {PageHeader, PageContent} from '#/main/core/layout/page'
import {ToolPageContainer} from '#/main/core/tool/containers/page'

import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {select} from '#/main/core/tools/home/selectors'
import {PlayerNav} from '#/main/core/tools/home/player/components/nav'
import {ToolActions} from '#/main/core/tools/home/components/tool'


const PlayerComponent = props =>
  <ToolPageContainer>
    {1 < props.sortedTabs.length &&
      <PlayerNav
        tabs={props.sortedTabs}
      />
    }
    <PageHeader
      className={props.currentTab.centerTitle ? 'center-page-title' : ''}
      title={props.currentTab ? props.currentTab.longTitle : ('desktop' === props.context.type ? trans('desktop') : props.context.data.name)}
    >
      {props.editable &&
          <ToolActions />
      }
    </PageHeader>
    <PageContent>
      <WidgetGrid
        context={props.context}
        widgets={props.widgets}
      />
    </PageContent>
  </ToolPageContainer>

PlayerComponent.propTypes = {
  context: T.object.isRequired,
  sortedTabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTab: T.shape(TabTypes.propTypes),
  editable: T.bool.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  )).isRequired
}

const Player = connect(
  (state) => ({
    context: select.context(state),
    editable: select.editable(state),
    sortedTabs: select.sortedTabs(state),
    currentTab: select.currentTab(state),
    widgets: select.widgets(state)
  })
)(PlayerComponent)

export {
  Player
}
