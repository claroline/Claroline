import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {PageContainer, PageHeader, PageContent, PageActions, PageAction} from '#/main/core/layout/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {WidgetGrid} from '#/main/core/widget/player/components/grid'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {selectors} from '#/main/core/tools/home/selectors'
import {Tabs} from '#/main/core/tools/home/components/tabs'


const PlayerComponent = props =>
  <PageContainer>
    {1 < props.tabs.filter(tab => tab.locked === true).length &&
      <Tabs
        tabs={props.tabs.filter(tab => tab.locked === true)}
        context={props.context}
        editing={false}
      />
    }
    <PageHeader
      className={props.currentTab.centerTitle ? 'center-page-title' : ''}
      title={props.currentTab ? props.currentTab.longTitle : ('desktop' === props.context.type ? trans('desktop') : props.context.data.name)}
      poster={props.currentTab.poster ? props.currentTab.poster.url: undefined}
    >
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
    tabs: selectors.tabs(state),
    currentTab: selectors.currentTab(state),
    widgets: selectors.widgets(state)
  })
)(PlayerComponent)

export {
  Player
}
