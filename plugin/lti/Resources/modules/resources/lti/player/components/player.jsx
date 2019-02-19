import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {HtmlText} from '#/main/core/layout/components/html-text'
import {Section} from '#/main/app/content/components/sections'

import {selectors} from '#/plugin/lti/resources/lti/store'
import {LtiResource as LtiResourceType} from '#/plugin/lti/resources/lti/prop-types'

class PlayerComponent extends Component {
  componentDidMount() {
    if (this.props.ltiResource && this.props.ltiResource.ltiApp && this.ltiFormRef) {
      this.ltiFormRef.submit()
    }
  }

  render() {
    return (
      <div>
        {this.props.ltiResource && !this.props.ltiResource.ltiApp &&
          <div className="alert alert-warning">
            {trans('unconfigured_resource', {}, 'lti')}
          </div>
        }
        {this.props.ltiResource && this.props.ltiResource.ltiApp &&
          <Section
            className="form-section embedded-list-section"
            title={this.props.ltiResource.ltiApp.title}
          >
            <HtmlText>{this.props.ltiResource.ltiApp.description}</HtmlText>
          </Section>
        }
        {this.props.ltiResource && this.props.ltiResource.ltiApp &&
          <form
            id={`form_app_${this.props.ltiResource.ltiApp.id}`}
            ref={el => this.ltiFormRef = el}
            method="POST"
            target={this.props.ltiResource.openInNewTab ? '_blank' : 'lti'}
            action={this.props.ltiResource.ltiApp.url}
          >
            {Object.keys(this.props.ltiResource.ltiData).map(key =>
              <input
                key={`lti_input_${key}`}
                type="hidden"
                name={key}
                value={this.props.ltiResource.ltiData[key]}
              />
            )}
          </form>
        }

        <div
          id="frameLti"
          className="content-container claro-iframe-content-container"
          style={this.props.ltiResource.ratio ?
            {
              position: 'relative',
              paddingBottom: `${this.props.ltiResource.ratio}%`
            } :
            {}
          }
        >
          <iframe
            className="claro-iframe"
            name="lti"
          >
          </iframe>
        </div>
      </div>
    )
  }
}

PlayerComponent.propTypes = {
  ltiResource: T.shape(LtiResourceType.propTypes).isRequired
}

const Player = connect(
  state => ({
    ltiResource: selectors.ltiResource(state)
  })
)(PlayerComponent)

export {
  Player
}
