import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {SummarizedContent} from '#/main/app/content/summary/components/content'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import {APIClass} from '#/plugin/scorm/resources/scorm/player/api'
import {Scorm as ScormType, Sco as ScoType} from '#/plugin/scorm/resources/scorm/prop-types'
import {selectors} from '#/plugin/scorm/resources/scorm/store'
import {flattenScos, getFirstOpenableSco, generateSummary} from '#/plugin/scorm/resources/scorm/utils'

class PlayerComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSco: getFirstOpenableSco(props.scos)
    }
    this.openSco = this.openSco.bind(this)
  }

  componentDidMount() {
    this.props.initializeScormAPI(this.state.currentSco, this.props.scorm, this.props.trackings)
  }

  openSco(sco) {
    this.setState({currentSco: sco})
    this.props.initializeScormAPI(sco, this.props.scorm, this.props.trackings)
  }

  render() {
    return (
      1 < this.props.scos.length ?
        <SummarizedContent
          className="scorm-summary"
          summary={{
            displayed: true,
            opened: true,
            pinned: true,
            title: trans('summary'),
            links: generateSummary(this.props.scorm.scos, this.openSco)
          }}
        >
          <div
            className="content-container scorm-content-container"
            style={this.props.scorm.ratio ?
              {
                position: 'relative',
                paddingBottom: `${this.props.scorm.ratio}%`
              } :
              {}
            }
          >
            <iframe
              className="scorm-iframe"
              src={`${asset('uploads/scorm/')}${this.props.workspaceUuid}/${this.props.scorm.hashName}/${this.state.currentSco.data.entryUrl}${this.state.currentSco.data.parameters ? this.state.currentSco.data.parameters : ''}`}
            />
          </div>
        </SummarizedContent> :
        <div
          className="content-container scorm-content-container"
          style={this.props.scorm.ratio ?
            {
              position: 'relative',
              paddingBottom: `${this.props.scorm.ratio}%`
            } :
            {}
          }
        >
          <iframe
            className="scorm-iframe"
            src={`${asset('uploads/scorm/')}${this.props.workspaceUuid}/${this.props.scorm.hashName}/${this.state.currentSco.data.entryUrl}${this.state.currentSco.data.parameters ? this.state.currentSco.data.parameters : ''}`}
          />
        </div>
    )
  }
}

PlayerComponent.propTypes = {
  scorm: T.shape(ScormType.propTypes),
  trackings: T.object,
  scos: T.arrayOf(T.shape(ScoType.propTypes)).isRequired,
  workspaceUuid: T.string.isRequired,
  initializeScormAPI: T.func.isRequired
}

const Player = connect(
  state => ({
    scorm: selectors.scorm(state),
    trackings: selectors.trackings(state),
    scos: flattenScos(selectors.scos(state)),
    workspaceUuid: resourceSelect.resourceNode(state).workspace.id
  }),
  dispatch => ({
    initializeScormAPI(sco, scorm, tracking) {
      window.API = new APIClass(sco, scorm, tracking[sco.id], dispatch)
      window.api = new APIClass(sco, scorm, tracking[sco.id], dispatch)
      window.API_1484_11 = new APIClass(sco, scorm, tracking[sco.id], dispatch)
      window.api_1484_11 = new APIClass(sco, scorm, tracking[sco.id], dispatch)
    }
  })
)(PlayerComponent)

export {
  Player
}
