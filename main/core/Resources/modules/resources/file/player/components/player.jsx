import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {theme} from '#/main/app/config'
import {Await} from '#/main/app/components/await'
import {getFile} from '#/main/core/files'

import {File as FileTypes} from '#/main/core/files/prop-types'
import {selectors} from '#/main/core/resources/file/store'

// TODO : find a way to make this kind of component generic (duplicated for all apps coming from dynamic loading)
// TODO : display a standard player with file info if no custom one

class Player extends Component {
  constructor(props) {
    super(props)

    this.state = {
      filePlayer: null,
      fileStyles: null
    }
  }

  render() {
    return (
      <Await
        for={getFile(this.props.mimeType)}
        then={module => this.setState({
          filePlayer: get(module, 'fileType.components.player') || null,
          fileStyles: get(module, 'fileType.styles') || null
        })}
      >
        <div>
          {this.state.filePlayer && React.createElement(this.state.filePlayer, {
            file: this.props.file
          })}

          {this.state.fileStyles &&
            <link rel="stylesheet" type="text/css" href={theme(this.state.fileStyles)} />
          }
        </div>
      </Await>
    )
  }
}

Player.propTypes = {
  mimeType: T.string.isRequired,
  file: T.shape(
    FileTypes.propTypes
  ).isRequired
}

const FilePlayer = connect(
  (state) => ({
    mimeType: selectors.mimeType(state),
    file: selectors.file(state)
  })
)(Player)

export {
  FilePlayer
}
