import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEqual from 'lodash/isEqual'

import {mount, unmount} from '#/main/app/mount'

import {getWidget} from '#/main/core/widget/types'
import {reducer} from '#/main/core/widget/content/store'
import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'

// the class is because of the use of references
class WidgetContent extends Component {
  componentDidMount() {
    this.mountWidget(this.props.instance, this.props.context)
  }

  componentWillReceiveProps(nextProps) {
    // the embedded resource has changed
    if (!isEqual(this.props.instance, nextProps.instance)) {
      // remove old app
      unmount(this.mountNode)

      this.mountWidget(nextProps.instance, nextProps.context)
    }
  }

  mountWidget(instance, context) {
    getWidget(instance.type).then(module => {
      const WidgetApp = new module.App()

      mount(this.mountNode, WidgetApp.component, reducer, {
        instance: instance,
        context: context
      })
    })
  }

  render() {
    return (
      <div ref={element => this.mountNode = element} />
    )
  }
}

WidgetContent.propTypes = {
  context: T.object.isRequired,
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired
}

export {
  WidgetContent
}
