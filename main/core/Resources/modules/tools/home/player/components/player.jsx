import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {PageContainer, PageHeader, PageContent, PageActions, PageAction} from '#/main/core/layout/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {WidgetGrid} from '#/main/core/widget/player/components/grid'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {selectors} from '#/main/core/tools/home/store'
import {selectors as playerSelectors} from '#/main/core/tools/home/player/store'
import {Tabs} from '#/main/core/tools/home/components/tabs'

const PlayerComponent = props =>
  <PageContainer>
    <PageHeader
      className={props.currentTab.centerTitle ? 'text-center' : ''}
      title={props.currentTab ? props.currentTab.longTitle : ('desktop' === props.context.type ? trans('desktop') : props.context.data.name)}
      poster={props.currentTab.poster ? props.currentTab.poster.url: undefined}
    >
      {1 < props.tabs.length &&
        <Tabs
          tabs={props.tabs}
          context={props.context}
          editing={false}
        />
      }

      {props.editable &&
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
        context={props.context}
        widgets={props.widgets}
      />
    </PageContent>
  </PageContainer>

PlayerComponent.propTypes = {
  context: T.object.isRequired,
  tabs: T.arrayOf(T.shape(
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
    context: selectors.context(state),
    editable: selectors.editable(state),
    tabs: playerSelectors.tabs(state),
    currentTab: selectors.currentTab(state),
    widgets: selectors.widgets(state)
  })
)(PlayerComponent)

export {
  Player
}
