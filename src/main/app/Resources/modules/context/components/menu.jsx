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

import {ContextUser} from '#/main/app/context/containers/user'
import {ContextNav} from '#/main/app/context/containers/nav'
import {getActions} from '#/main/app/context/utils'
import {route} from '#/main/app/context/routing'

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
          target: this.props.path + '/' + tool.name,
          status: tool.status,
          subscript: (tool.status || 0 === tool.status) ? {
            type: 'label',
            value: tool.status,
            status: 0 === tool.status ? 'secondary' : 'primary'
          } : undefined
        }))
    }

    let actions
    if (!isEmpty(this.props.contextData)) {
      actions = getActions(this.props.contextType, [this.props.contextData], {
        update: this.props.reload,
        delete() {
          this.props.history.push(route(this.props.contextType))
        }
      }, this.props.path, this.props.currentUser)
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

                {!this.props.notFound && !this.props.hasErrors && actions &&
                  <Toolbar
                    id="app-menu-actions"
                    className="flex-shrink-0"
                    buttonName="btn"
                    actions={actions}
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
        <div className="app-menu-backdrop" role="presentation" onClick={this.props.close} aria-hidden={true} />
      </>
    )
  }
}

ContextMenu.propTypes = {
  path: T.string,
  title: T.node.isRequired,
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  })),
  children: T.node,

  // from store
  contextData: T.object,
  contextType: T.string,
  notFound: T.bool.isRequired,
  hasErrors: T.bool.isRequired,
  opened: T.bool.isRequired,
  untouched: T.bool.isRequired,
  close: T.func.isRequired,
  reload: T.func.isRequired
}

ContextMenu.defaultProps = {
  path: '',
  actions: []
}

export {
  ContextMenu
}
