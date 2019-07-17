import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {PageSimple} from '#/main/app/page/components/simple'
import {
  PageHeader,
  PageContent,
  PageGroupActions,
  PageActions,
  PageAction,
  MoreAction
} from '#/main/core/layout/page'
import {CALLBACK_BUTTON, MODAL_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {MODAL_WALKTHROUGHS} from '#/main/app/overlays/walkthrough/modals/walkthroughs'

import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'
import {getWalkthroughs} from '#/main/core/tools/home/walkthroughs'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {Tabs} from '#/main/core/tools/home/components/tabs'

import {EditorForm} from '#/main/core/tools/home/editor/components/form'

const EditorTab = props =>
  <PageSimple
    className="home-tool"
    showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
    path={[].concat(getToolBreadcrumb('home', props.currentContext.type, props.currentContext.data), props.currentTab ? [{
      label: props.currentTab.longTitle,
      target: '/' // this don't work but it's never used as current tab is always last for now
    }] : [])}
  >
    <PageHeader
      alignTitle={props.currentTab && props.currentTab.centerTitle ? 'center' : 'left'}
      title={props.currentTabTitle}
      poster={props.currentTab && props.currentTab.poster ? props.currentTab.poster.url: undefined}
    >
      <Tabs
        prefix={`${props.path}/edit`}
        tabs={props.tabs}
        create={() => props.createTab(props.currentContext, props.administration, props.tabs.length, props.history.push)}
        currentContext={props.currentContext}
        editing={true}
      />

      <PageActions>
        <PageGroupActions>
          <PageAction
            type={LINK_BUTTON}
            label={trans('configure', {}, 'actions')}
            icon="fa fa-fw fa-cog"
            target={`${props.path}/edit`}
            disabled={true}
            primary={true}
          />
        </PageGroupActions>

        <PageGroupActions>
          <MoreAction
            actions={[
              {
                name: 'walkthrough',
                type: MODAL_BUTTON,
                icon: 'fa fa-street-view',
                label: trans('show-walkthrough', {}, 'actions'),
                modal: [MODAL_WALKTHROUGHS, {
                  walkthroughs: getWalkthroughs(props.currentTab, (field, value) => props.updateTab(props.currentTabIndex, field, value))
                }]
              }, {
                type: CALLBACK_BUTTON,
                label: trans('delete', {}, 'actions'),
                icon: 'fa fa-fw fa-trash-o',
                dangerous: true,
                confirm: {
                  title: trans('home_tab_delete_confirm_title'),
                  message: trans('home_tab_delete_confirm_message'),
                  subtitle: props.currentTab && props.currentTab.title
                },
                disabled: props.readOnly || 1 >= props.tabs.length,
                callback: () => props.deleteTab(props.tabs, props.currentTab, props.history.push)
              }
            ]}
          />
        </PageGroupActions>
      </PageActions>
    </PageHeader>

    <PageContent>
      <EditorForm
        path={props.path}
        currentUser={props.currentUser}
        currentContext={props.currentContext}
        currentTabIndex={props.currentTabIndex}
        currentTab={props.currentTab}
        widgets={props.widgets}
        administration={props.administration}
        readOnly={props.readOnly}
        tabs={props.tabs}

        update={props.updateTab}
        move={props.moveTab}
        setErrors={props.setErrors}
      />
    </PageContent>
  </PageSimple>

EditorTab.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  currentContext: T.object.isRequired,
  administration: T.bool.isRequired,
  readOnly: T.bool.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTabTitle: T.string,
  currentTab: T.shape(TabTypes.propTypes),
  currentTabIndex: T.number.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  )).isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  createTab: T.func.isRequired,
  updateTab: T.func.isRequired,
  setErrors: T.func.isRequired,
  deleteTab: T.func.isRequired,
  moveTab: T.func.isRequired
}

export {
  EditorTab
}
