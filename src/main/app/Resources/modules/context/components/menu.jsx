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
import {ContextNav} from '#/main/app/context/containers/nav'

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

      if (constants.SIZE_XL > newSize) {
        this.props.close()
      }
    }
  }

  autoClose() {
    // only auto close on small windows
    if (constants.SIZE_XL > this.state.computedSize) {
      this.props.close()
    }
  }

  render() {
    // TODO : create selector
    let toolLinks = []
    if (!this.props.notFound && !this.props.hasErrors) {
      toolLinks = this.props.tools
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
    }

    return (
      <>
      <aside
        role="navigation"
        className={classes('app-toolbar', {
          show: this.props.opened
        })}
      >
        <ContextNav />
        <section className={classes('app-menu', {
          show: this.props.opened
        })}>
          {this.props.title &&
            <header className="app-menu-header m-3 ms-4 me-1 d-flex align-items-center justify-content-between">
              <h1 className="app-menu-title text-truncate d-block">{this.props.title}</h1>

              {!this.props.notFound && !this.props.hasErrors && (!isEmpty(this.props.actions) || !Array.isArray(this.props.actions)) &&
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

          {1 < toolLinks.length &&
            <Toolbar
              className="app-menu-items"
              buttonName="app-menu-item"
              actions={toolLinks}
              onClick={this.autoClose}
            />
          }
        </section>
      </aside>
      <div className="app-menu-backdrop" role="presentation" onClick={this.props.close} />
      </>
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
  notFound: T.bool.isRequired,
  hasErrors: T.bool.isRequired,
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
