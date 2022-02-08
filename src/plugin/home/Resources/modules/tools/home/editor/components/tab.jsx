import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Form} from '#/main/app/content/form/containers/form'

import {MODAL_HOME_CREATION} from '#/plugin/home/tools/home/editor/modals/creation'
import {MODAL_HOME_PARAMETERS} from '#/plugin/home/tools/home/editor/modals/parameters'
import {MODAL_HOME_POSITION} from '#/plugin/home/tools/home/editor/modals/position'
import {HomePage} from '#/plugin/home/tools/home/containers/page'
import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {flattenTabs} from '#/plugin/home/tools/home/utils'
import {getTab} from '#/plugin/home/home'

import {selectors} from '#/plugin/home/tools/home/editor/store/selectors'
import {getFormDataPart} from '#/plugin/home/tools/home/editor/utils'

class EditorTab extends Component {
  constructor(props) {
    super(props)

    this.state = {
      parameters: null
    }
  }

  componentDidMount() {
    if (this.props.currentTab) {
      getTab(this.props.currentTab.type).then(tabApp => this.setState({
        parameters: tabApp.parameters
      }))
    }
  }

  componentDidUpdate(prevProps) {
    if (this.props.currentTab && get(prevProps, 'currentTab.type') !== get(this.props, 'currentTab.type')) {
      getTab(this.props.currentTab.type).then(tabApp => this.setState({
        parameters: tabApp.parameters
      }))
    }
  }

  renderParameters() {
    if (this.props.currentTab && this.state.parameters) {
      return createElement(this.state.parameters, {
        path: `${this.props.path}/${this.props.currentTab ? this.props.currentTab.slug : ''}`,
        readOnly: this.props.readOnly,
        currentContext: this.props.currentContext,
        tabs: this.props.tabs,
        currentTab: this.props.currentTab,
        title: this.props.currentTabTitle,
        update: (prop, data, tabId = null) => {
          if (tabId === null) {
            tabId = this.props.currentTab.id
          }

          this.props.updateTab(this.props.tabs, tabId, data, 'parameters.' + prop)
        }
      })
    }

    return null
  }

  render() {
    if (!this.props.currentTab) {
      return null
    }

    return (
      <HomePage
        path="/edit"
        tabs={this.props.tabs}
        currentTab={this.props.currentTab}
        title={this.props.currentTabTitle}
        showHidden={true}
        actions={[
          {
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_tab', {}, 'home'),
            modal: [MODAL_HOME_CREATION, {
              position: this.props.tabs.length,
              create: (tab) => this.props.createTab(null, tab, (slug) => this.props.history.push(`${this.props.path}/edit/${slug}`))
            }],
            primary: true,
            group: trans('management')
          }, {
            name: 'add-sub',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('tab_add_child', {}, 'home'),
            disabled: 'administration' === this.props.currentTab.context && !this.props.administration,
            displayed: !this.props.currentTab.parent, // only allow one sub-level of tabs
            modal: [MODAL_HOME_CREATION, {
              position: this.props.tabs.length,
              create: (tab) => this.props.createTab(this.props.currentTab, tab, (slug) => this.props.history.push(`${this.props.path}/edit/${slug}`))
            }],
            group: trans('management')
          }, {
            name: 'configure',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-cog',
            label: trans('configure', {}, 'actions'),
            disabled: this.props.readOnly,
            modal: [MODAL_HOME_PARAMETERS, {
              tab: this.props.currentTab,
              save: (tab) => this.props.updateTab(this.props.tabs, tab.id, tab)
            }],
            group: trans('management')
          }, {
            name: 'move',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-arrows',
            label: trans('move', {}, 'actions'),
            disabled: this.props.readOnly || 1 >= this.props.tabs.length,
            modal: [MODAL_HOME_POSITION, {
              tab: this.props.currentTab,
              tabs: flattenTabs(this.props.tabs),
              selectAction: (position) => ({
                type: CALLBACK_BUTTON,
                label: trans('move', {}, 'actions'),
                callback: () => this.props.moveTab(this.props.currentTab.id, position)
              })
            }],
            group: trans('management')
          }, {
            name: 'delete',
            type: CALLBACK_BUTTON,
            label: trans('delete', {}, 'actions'),
            icon: 'fa fa-fw fa-trash-o',
            dangerous: true,
            confirm: {
              title: trans('home_tab_delete_confirm_title', {}, 'home'),
              message: trans('home_tab_delete_confirm_message', {}, 'home'),
              subtitle: this.props.currentTabTitle
            },
            disabled: this.props.readOnly || 1 >= this.props.tabs.length,
            callback: () => this.props.deleteTab(this.props.tabs, this.props.currentTab),
            group: trans('management')
          }
        ]}
      >
        <Form
          name={selectors.FORM_NAME}
          dataPart={getFormDataPart(this.props.currentTab.id, this.props.tabs)}
          buttons={true}
          lock={!this.props.readOnly && this.props.currentTab && !get(this.props.currentTab, '_new', false) ? {
            id: this.props.currentTab.id,
            className: 'Claroline\\HomeBundle\\Entity\\HomeTab'
          } : undefined}
          target={[this.props.administration ? 'apiv2_home_admin' : 'apiv2_home_update', {
            context: this.props.currentContext.type,
            contextId: !isEmpty(this.props.currentContext.data) ? this.props.currentContext.data.id : get(this.props.currentUser, 'id')
          }]}
          cancel={{
            type: LINK_BUTTON,
            target: `${this.props.path}/${this.props.currentTab ? this.props.currentTab.slug : ''}`,
            exact: true
          }}
        >
          {this.props.readOnly &&
            <AlertBlock
              type="warning"
              title={trans('home_tab_locked', {}, 'home')}
            >
              {trans('home_tab_locked_message', {}, 'home')}
            </AlertBlock>
          }

          {this.renderParameters()}
        </Form>
      </HomePage>
    )
  }
}

EditorTab.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
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
  createTab: T.func.isRequired,
  updateTab: T.func.isRequired,
  moveTab: T.func.isRequired,
  deleteTab: T.func.isRequired
}

export {
  EditorTab
}
