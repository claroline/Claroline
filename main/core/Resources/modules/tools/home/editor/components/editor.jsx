import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {ToolPageContainer} from '#/main/core/tool/containers/page'
import {actions as formActions} from '#/main/core/data/form/actions'
import {
  PageHeader,
  PageContent,
  PageGroupActions,
  PageActions,
  PageAction
} from '#/main/core/layout/page'
import {WidgetGridEditor} from '#/main/core/widget/editor/components/grid'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

import {ToolActions} from '#/main/core/tools/home/components/tool'
import {Tab as TabTypes} from '#/main/core/tools/home/prop-types'
import {select} from '#/main/core/tools/home/selectors'
import {actions} from '#/main/core/tools/home/editor/actions'
import {select as editorSelect} from '#/main/core/tools/home/editor/selectors'
import {MODAL_TAB_PARAMETERS} from '#/main/core/tools/home/editor/modals/parameters'
import {EditorNav} from '#/main/core/tools/home/editor/components/nav'



const EditorComponent = props =>
  <ToolPageContainer>
    <EditorNav />
    <PageHeader
      className={props.currentTab.centerTitle ? 'center-page-title' : ''}
      title={props.currentTab ? props.currentTab.longTitle : ('desktop' === props.context.type ? trans('desktop') : props.context.data.name)}
    >
      <div className="tab-edition-container">
        <PageActions>
          <PageGroupActions>
            <PageAction
              type="modal"
              label={trans('configure', {}, 'actions')}
              icon="fa fa-fw fa-cog"
              modal={[MODAL_TAB_PARAMETERS, {
                currentTabData: props.currentTab,
                save: (Formdata) => props.updateTab(props.currentTabIndex, Formdata)
              }]}
            />
            <PageAction
              type="callback"
              label={trans('delete')}
              icon="fa fa-fw fa-trash-o"
              dangerous={true}
              confirm={{
                title: trans('home_tab_delete_confirm_title'),
                message: trans('home_tab_delete_confirm_message')
              }}
              callback={() => props.deleteTab(props.currentTabIndex, props.tabs, props.history.push)}
            />
          </PageGroupActions>
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
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTab: T.shape(TabTypes.propTypes),
  history: T.object.isRequired,
  deleteTab: T.func,
  updateTab: T.func,
  currentTabIndex: T.number.isRequired
}

const Editor = connect(
  state => ({
    context: select.context(state),
    tabs: editorSelect.editorData(state),
    widgets: editorSelect.widgets(state),
    currentTabIndex: editorSelect.currentTabIndex(state),
    currentTab: editorSelect.currentTab(state)
  }),
  dispatch => ({
    update(currentTabIndex, widgets) {
      dispatch(formActions.updateProp('editor', `[${currentTabIndex}].widgets`, widgets))
    },
    updateTab(currentTabIndex, tab) {
      dispatch(formActions.updateProp('editor', `[${currentTabIndex}]`, tab))
    },
    deleteTab(currentTabIndex, tabs, push) {
      dispatch(actions.deleteTab(currentTabIndex, tabs, push))
    }
  })
)(EditorComponent)

export {
  Editor
}
