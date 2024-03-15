import React, {Children, cloneElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import isNumber from 'lodash/isNumber'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security/permissions'
import {getWindowSize, constants} from '#/main/app/dom/size'
import {Toolbar} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'

import {ContextUser} from '#/main/app/context/containers/user'

class ContextMenu extends Component
{
  constructor(props) {
    super(props)

    this.state = {
      computedSize: getWindowSize()
    }

    this.resize = this.resize.bind(this)
    this.autoClose = this.autoClose.bind(this)
  }

  componentDidMount() {
    window.addEventListener('resize', this.resize)

    if (this.props.untouched) {
      this.autoClose()
    }
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.resize)
  }

  resize() {
    const newSize = getWindowSize()
    if (newSize !== this.state.computedSize) {
      this.setState({computedSize: newSize})

      this.autoClose()
    }
  }

  autoClose() {
    // only auto close on small windows
    if (constants.SIZE_SM === this.state.computedSize || constants.SIZE_XS === this.state.computedSize) {
      this.props.close()
    }
  }

  render() {
    // TODO : create selector
    const toolActions = this.props.tools
      .filter(tool => hasPermission('open', tool) && !get(tool, 'restrictions.hidden', false))
      .sort((a, b) => {
        if (isNumber(a.order) && isNumber(b.order) && a.order !== b.order) {
          return a.order - b.order
        }

        if (trans(a.name, {}, 'tools') > trans(b.name, {}, 'tools')) {
          return 1
        }

        return -1
      })
      .map(tool => ({
        name: tool.name,
        type: LINK_BUTTON,
        icon: `fa fa-fw fa-${tool.icon}`,
        label: trans(tool.name, {}, 'tools'),
        target: this.props.path + '/' + tool.name
      }))


    return (
      <aside className={classes('app-menu', {
        show: this.props.opened
      })}>
        {this.props.title &&
          <header className="app-menu-header m-3 ms-4 me-1 d-flex align-items-center justify-content-between">
            <h1 className="app-menu-title text-truncate d-block">{this.props.title}</h1>

            {(!isEmpty(this.props.actions) || !Array.isArray(this.props.actions)) &&
              <Toolbar
                id="app-menu-actions"
                className="flex-shrink-0"
                buttonName="btn"
                actions={this.props.actions}
                onClick={this.autoClose}
                toolbar="favourite more"
                tooltip="bottom"
              />
            }
          </header>
        }

        <ContextUser />

        {this.props.children && Children.map(this.props.children, child => child && cloneElement(child, {
          autoClose: this.autoClose
        }))}

        {1 < toolActions.length &&
          <Toolbar
            className="app-menu-items"
            buttonName="app-menu-item"
            actions={toolActions}
            onClick={this.autoClose}
          />
        }
      </aside>
    )
  }
}

ContextMenu.propTypes = {
  path: T.string,
  title: T.node.isRequired,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  })),
  children: T.node,

  // from store
  opened: T.bool.isRequired,
  untouched: T.bool.isRequired,
  close: T.func.isRequired
}

ContextMenu.defaultProps = {
  path: '',
  actions: []
}

export {
  ContextMenu
}
