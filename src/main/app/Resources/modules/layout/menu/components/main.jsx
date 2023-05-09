import React, {Fragment, Component, Children, cloneElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import isNumber from 'lodash/isNumber'

import RootCloseWrapper from 'react-overlays/lib/RootCloseWrapper'

import {getWindowSize} from '#/main/app/dom/size/utils'
import {constants} from '#/main/app/dom/size/constants'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'
import {LINK_BUTTON} from '#/main/app/buttons'

import {MenuSection} from '#/main/app/layout/menu/components/section'

class MenuMain extends Component {
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
    return (
      <RootCloseWrapper
        disabled={constants.SIZE_SM !== this.state.computedSize && constants.SIZE_XS !== this.state.computedSize}
        onRootClose={() => this.props.close()}
      >
        <Fragment>
          <aside className="app-menu">
            <header className="app-menu-header">
              {this.props.backAction &&
                <Button
                  {...this.props.backAction}
                  id="app-menu-back"
                  className="app-menu-back"
                  icon="fa fa-arrow-up"
                  tooltip="right"
                  onClick={this.autoClose}
                />
              }

              {this.props.title &&
                <h1 className="app-menu-title h5">{this.props.title}</h1>
              }
            </header>

            {this.props.children && Children.map(this.props.children, child => child && cloneElement(child, {
              autoClose: this.autoClose
            }))}

            {0 !== this.props.tools.length &&
              <MenuSection
                className="tools"
                icon="fa fa-fw fa-tools"
                title={trans('tools')}
                opened={'tools' === this.props.section}
                toggle={() => this.props.changeSection('tools')}
              >
                <Toolbar
                  className="list-group"
                  buttonName="list-group-item"
                  actions={this.props.tools
                    .filter((tool) => undefined === tool.displayed || tool.displayed)
                    .map((tool) => ({
                      name: tool.name,
                      type: LINK_BUTTON,
                      icon: `fa fa-fw fa-${tool.icon}`,
                      label: trans(tool.name, {}, 'tools'),
                      target: tool.path,
                      order: tool.order
                    }))
                    .sort((a, b) => {
                      if (isNumber(a.order) && isNumber(b.order) && a.order !== b.order) {
                        return a.order - b.order
                      }

                      if (a.label > b.label) {
                        return 1
                      }

                      return -1
                    })
                  }
                  onClick={this.autoClose}
                />
              </MenuSection>
            }

            {(!isEmpty(this.props.actions) || !Array.isArray(this.props.actions)) &&
              <MenuSection
                className="actions"
                icon="fa fa-fw fa-ellipsis-v"
                title={trans('more')}
                opened={'actions' === this.props.section}
                toggle={() => this.props.changeSection('actions')}
              >
                <Toolbar
                  id="app-menu-actions"
                  className="list-group"
                  buttonName="list-group-item"
                  actions={this.props.actions}
                  onClick={this.autoClose}
                />
              </MenuSection>
            }
          </aside>

          {(constants.SIZE_SM === this.state.computedSize || constants.SIZE_XS === this.state.computedSize) &&
            <div className="app-menu-backdrop" />
          }
        </Fragment>
      </RootCloseWrapper>
    )
  }
}
  

MenuMain.propTypes = {
  title: T.string,
  backAction: T.shape(ActionTypes.propTypes),

  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    path: T.string.isRequired,
    displayed: T.bool,
    order: T.number
  })),
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

  children: T.node,

  untouched: T.bool.isRequired,
  section: T.oneOf(['tool', 'history', 'tools', 'actions']),
  changeSection: T.func.isRequired,
  close: T.func.isRequired
}

MenuMain.defaultProps = {
  tools: [],
  actions: []
}

export {
  MenuMain
}
