import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {PageSimple} from '#/main/app/page/components/simple'
import {
  PageHeader,
  PageContent,
  PageGroupActions,
  PageActions,
  MoreAction
} from '#/main/core/layout/page'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {FormData} from '#/main/app/content/form/containers/data'

import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'
import {WidgetGrid} from '#/main/core/widget/player/components/grid'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {Tabs} from '#/main/core/tools/home/components/tabs'

import {EditorForm} from '#/main/core/tools/home/editor/components/form'
import {selectors} from '#/main/core/tools/home/editor/store/selectors'

const EditorTab = props =>
  <PageSimple
    className="home-tool"
    showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
    path={[].concat(getToolBreadcrumb('home', props.currentContext.type, props.currentContext.data), props.currentTab ? [{
      label: props.currentTab.longTitle,
      target: '/' // this don't work but it's never used as current tab is always last for now
    }] : [])}
    header={{
      title: `${trans('home', {}, 'tools')}${'workspace' === props.currentContext.type ? ' - ' + props.currentContext.data.code : ''}`,
      description: 'workspace' === props.currentContext.type && props.currentContext.data.meta ? props.currentContext.data.meta.description : null
    }}
  >
    <PageHeader
      alignTitle={props.currentTab && props.currentTab.centerTitle ? 'center' : 'left'}
      title={props.currentTabTitle}
      poster={props.currentTab && props.currentTab.poster ? props.currentTab.poster.url: undefined}
    >
      <Tabs
        prefix={`${props.path}/edit`}
        tabs={props.tabs}
        create={() => props.createTab(props.currentContext, props.administration, props.currentUser, props.tabs.length, (path) => props.history.push(props.path+path))}
        currentContext={props.currentContext}
      />

      <PageActions>
        <PageGroupActions>
          <MoreAction
            actions={[
              {
                type: CALLBACK_BUTTON,
                label: trans('delete', {}, 'actions'),
                icon: 'fa fa-fw fa-trash-o',
                dangerous: true,
                confirm: {
                  title: trans('home_tab_delete_confirm_title', {}, 'home'),
                  message: trans('home_tab_delete_confirm_message', {}, 'home'),
                  subtitle: props.currentTab && props.currentTab.title
                },
                disabled: props.readOnly || 1 >= props.tabs.length,
                callback: () => props.deleteTab(props.tabs, props.currentTab, (path) => props.history.push(props.path+path))
              }
            ]}
          />
        </PageGroupActions>
      </PageActions>
    </PageHeader>

    <PageContent>
      {props.readOnly &&
        <AlertBlock
          type="warning"
          title={trans('home_tab_locked', {}, 'home')}
        >
          {trans('home_tab_locked_message', {}, 'home')}
        </AlertBlock>
      }

      {props.readOnly &&
        <FormData
          name={selectors.FORM_NAME}
          dataPart={`[${props.currentTabIndex}]`}
          buttons={true}
          lock={props.currentTab ? {
            id: props.currentTab.id,
            className: 'Claroline\\CoreBundle\\Entity\\Tab\\HomeTab'
          } : undefined}
          target={[props.administration ? 'apiv2_home_admin' : 'apiv2_home_update', {
            context: props.currentContext.type,
            contextId: !isEmpty(props.currentContext.data) ? props.currentContext.data.uuid : get(props.currentUser, 'id')
          }]}
          sections={[]}
          cancel={{
            type: LINK_BUTTON,
            target: `${props.path}/${props.currentTab ? props.currentTab.slug : ''}`,
            exact: true
          }}
        >
          <WidgetGrid
            currentContext={props.currentContext}
            widgets={props.widgets}
          />
        </FormData>
      }

      {!props.readOnly &&
        <EditorForm
          path={props.path}
          currentUser={props.currentUser}
          currentContext={props.currentContext}
          currentTabIndex={props.currentTabIndex}
          currentTab={props.currentTab}
          widgets={props.widgets}
          administration={props.administration}
          tabs={props.tabs}

          update={props.updateTab}
          move={props.moveTab}
          setErrors={props.setErrors}
        />
      }
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
