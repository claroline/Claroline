import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {Helmet} from 'react-helmet'
import isEqual from 'lodash/isEqual'

import {theme} from '#/main/theme/config'
import {mount, unmount} from '#/main/app/dom/mount'
import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getWidget} from '#/main/core/widget/types'
import {reducer} from '#/main/core/widget/content/store'
import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'

// the class is because of the use of references
class WidgetContent extends Component {
  constructor(props) {
    super(props)

    this.mountWidget = this.mountWidget.bind(this)
  }

  componentDidMount() {
    this.mountWidget()
  }

  componentDidUpdate(prevProps) {
    // the embedded resource has changed
    if (!isEqual(this.props.instance, prevProps.instance)) {
      if (prevProps.instance) {
        // remove old app
        unmount(this.mountNode)
      }

      setTimeout(this.mountWidget, 0)
    }
  }

  componentWillUnmount() {
    // remove old app
    unmount(this.mountNode)
  }

  mountWidget() {
    if (this.props.instance) {
      getWidget(this.props.instance.type).then(module => {
        const WidgetApp = new module.App()

        const WidgetAppComponent = () =>
          <div className="widget-content">
            {WidgetApp.styles && 0 !== WidgetApp.styles.length &&
              <Helmet>
                {WidgetApp.styles.map(styleName =>
                  <link key={styleName} rel="stylesheet" type="text/css" href={theme(styleName)} />
                )}
              </Helmet>
            }
            {createElement(WidgetApp.component)}
          </div>

        WidgetAppComponent.displayName = `WidgetApp(${this.props.instance.type})`

        mount(this.mountNode, WidgetAppComponent, reducer, {
          [securitySelectors.STORE_NAME]: {
            currentUser: this.props.currentUser,
            impersonated: this.props.impersonated
          },
          [configSelectors.STORE_NAME]: this.props.config,
          instance: this.props.instance,
          currentContext: this.props.currentContext
        }, true)
      })
    }
  }

  render() {
    return (
      <div ref={element => this.mountNode = element} className="widget-content-container" />
    )
  }
}

WidgetContent.propTypes = {
  currentContext: T.object.isRequired,
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired,

  // from store (to build the embedded store)
  currentUser: T.object,
  impersonated: T.bool,
  config: T.object
}

export {
  WidgetContent
}
