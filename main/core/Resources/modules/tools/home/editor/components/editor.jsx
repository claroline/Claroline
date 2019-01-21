import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {currentUser} from '#/main/app/security'
import {PageSimple} from '#/main/app/page/components/simple'
import {
  PageHeader,
  PageContent,
  PageGroupActions,
  PageActions,
  PageAction,
  MoreAction
} from '#/main/core/layout/page'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {CALLBACK_BUTTON, MODAL_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {MODAL_WALKTHROUGHS} from '#/main/app/overlay/walkthrough/modals/walkthroughs'

import {getToolPath, showToolBreadcrumb} from '#/main/core/tool/utils'
import {getWalkthroughs} from '#/main/core/tools/home/walkthroughs'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {selectors} from '#/main/core/tools/home/store'
import {actions as editorActions, selectors as editorSelectors} from '#/main/core/tools/home/editor/store'
import {Tabs} from '#/main/core/tools/home/components/tabs'

import {TabEditor} from '#/main/core/tools/home/editor/components/tab'

const EditorComponent = props =>
  <PageSimple
    className="home-tool"
    showBreadCrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
    path={[].concat(getToolPath('home', props.currentContext.type, props.currentContext.data), props.currentTab ? [{
      label: props.currentTab.longTitle,
      target: '/' // this don't work but it's never used as current tab is always last for now
    }] : [])}
  >
    <PageHeader
      alignTitle={true === props.currentTab.centerTitle ? 'center' : 'left'}
      title={props.currentTabTitle}
      poster={props.currentTab.poster ? props.currentTab.poster.url: undefined}
    >
      <Tabs
        prefix="/edit"
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
            target="/edit"
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
                  subtitle: props.currentTab.title
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
      <TabEditor
        currentContext={props.currentContext}
        currentTabIndex={props.currentTabIndex}
        currentTab={props.currentTab}
        widgets={props.widgets}
        administration={props.administration}
        readOnly={props.readOnly}
        created={0 !== props.playerTabs.filter(tab => props.currentTab.id === tab.id).length}
        tabs={props.tabs}

        update={props.updateTab}
        move={props.moveTab}
        setErrors={props.setErrors}
      />
    </PageContent>
  </PageSimple>

EditorComponent.propTypes = {
  currentContext: T.object.isRequired,
  administration: T.bool.isRequired,
  readOnly: T.bool.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  playerTabs: T.arrayOf(T.shape(
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

const Editor = connect(
  (state) => ({
    currentContext: selectors.context(state),
    administration: selectors.administration(state),
    readOnly: editorSelectors.readOnly(state),
    tabs: editorSelectors.editorTabs(state),
    playerTabs: selectors.tabs(state),
    widgets: editorSelectors.widgets(state),
    currentTabIndex: editorSelectors.currentTabIndex(state),
    currentTabTitle: selectors.currentTabTitle(state),
    currentTab: editorSelectors.currentTab(state)
  }),
  (dispatch) => ({
    updateTab(currentTabIndex, field, value) {
      dispatch(formActions.updateProp('editor', `[${currentTabIndex}].${field}`, value))
    },
    setErrors(errors) {
      dispatch(formActions.setErrors('editor', errors))
    },
    createTab(context, administration, position, navigate){
      const newTabId = makeId()

      dispatch(formActions.updateProp('editor', `[${position}]`, merge({}, TabTypes.defaultProps, {
        id: newTabId,
        title: trans('tab'),
        longTitle: trans('tab'),
        position: position + 1,
        type: administration ? 'administration' : context.type,
        administration: administration,
        user: context.type === 'desktop' && !administration ? currentUser() : null,
        workspace: context.type === 'workspace' ? {uuid: context.data.uuid} : null
      })))

      // open new tab
      navigate(`/edit/tab/${newTabId}`)
    },
    moveTab(tabs, currentTab, newPosition) {
      dispatch(editorActions.moveTab(tabs, currentTab, newPosition))
    },
    deleteTab(tabs, currentTab, navigate) {
      let tabIndex = tabs.findIndex(tab => tab.id === currentTab.id)
      tabIndex === 0 ? tabIndex++: tabIndex--

      dispatch(editorActions.deleteTab(tabs, currentTab))
      const redirected = tabs[tabIndex]
      // redirect
      navigate('/edit/tab/' + redirected.id)
    }
  })
)(EditorComponent)

export {
  Editor
}
