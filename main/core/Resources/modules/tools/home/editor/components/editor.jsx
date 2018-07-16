import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {ToolPageContainer} from '#/main/core/tool/containers/page'
import {actions as formActions} from '#/main/core/data/form/actions'
import {
  PageHeader,
  PageContent,
  PageActions,
  PageAction
} from '#/main/core/layout/page'
import {WidgetGridEditor} from '#/main/core/widget/editor/components/grid'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

import {ToolActions} from '#/main/core/tools/home/components/tool-actions'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {selectors} from '#/main/core/tools/home/selectors'
import {actions as EditorActions} from '#/main/core/tools/home/editor/actions'
import {actions} from '#/main/core/tools/home/actions'
import {selectors as editorSelectors} from '#/main/core/tools/home/editor/selectors'
import {MODAL_TAB_PARAMETERS} from '#/main/core/tools/home/editor/modals/parameters'
import {EditorNav} from '#/main/core/tools/home/editor/components/nav'


const EditorComponent = props =>

  <ToolPageContainer>
    <EditorNav
      tabs={props.editorTabs}
      context={props.context}
      create={(data) => props.createTab(props.editorTabs.length, data)}
    />
    <PageHeader
      className={props.currentTab.centerTitle ? 'center-page-title' : ''}
      title={props.currentTab ? props.currentTab.longTitle : ('desktop' === props.context.type ? trans('desktop') : props.context.data.name)}
    >
      <div className="tab-edition-container">
        <PageActions>
          <PageAction
            type="modal"
            label={trans('configure', {}, 'actions')}
            icon="fa fa-fw fa-cog"
            modal={[MODAL_TAB_PARAMETERS, {
              currentTabData: props.currentTab,
              save: (Formdata) => props.updateTab(props.currentTabIndex, Formdata)
            }]}
          />
          {1 < props.editorTabs.length &&
                <PageAction
                  type="callback"
                  label={trans('delete')}
                  icon="fa fa-fw fa-trash-o"
                  confirm={{
                    title: trans('home_tab_delete_confirm_title'),
                    message: trans('home_tab_delete_confirm_message')
                  }}
                  callback={() => props.deleteTab(props.currentTabIndex, props.editorTabs, props.history.push)}
                />
          }
        </PageActions>
        <ToolActions />
      </div>
    </PageHeader>
    <PageContent>
      <WidgetGridEditor
        context={props.context}
        widgets={props.widgets}
        update={(widgets) => props.update(props.currentTabIndex, widgets)}
      />
    </PageContent>
  </ToolPageContainer>


EditorComponent.propTypes = {
  context: T.object.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  )).isRequired,
  update: T.func.isRequired,
  editorTabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  sortedTabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  setCurrentTab: T.func.isRequired,
  currentTab: T.shape(TabTypes.propTypes),
  editable: T.bool.isRequired,
  history: T.object.isRequired,
  createTab: T.func,
  deleteTab: T.func,
  updateTab: T.func,
  currentTabIndex: T.number.isRequired
}


const Editor = connect(
  state => ({
    context: selectors.context(state),
    // sortedTabs: editorSelectors.sortedTabs(state),
    editorTabs: editorSelectors.editorTabs(state),
    widgets: editorSelectors.widgets(state),
    currentTabIndex: editorSelectors.currentTabIndex(state),
    currentTab: editorSelectors.currentTab(state),
    editable: selectors.editable(state)
  }),
  dispatch => ({
    setCurrentTab(tab){
      dispatch(actions.setCurrentTab(tab))
    },
    update(currentTabIndex, widgets) {
      dispatch(formActions.updateProp('editor', `tabs[${currentTabIndex}].widgets`, widgets))
    },
    createTab(tabIndex, tab){
      dispatch(formActions.updateProp('editor', `tabs[${tabIndex}]`, tab))
    },
    updateTab(currentTabIndex, tab) {
      dispatch(formActions.updateProp('editor', `tabs[${currentTabIndex}]`, tab))
    },
    deleteTab(currentTabIndex, editorTabs, push) {
      dispatch(EditorActions.deleteTab(currentTabIndex, editorTabs, push))
    }
  })
)(EditorComponent)

export {
  Editor
}
