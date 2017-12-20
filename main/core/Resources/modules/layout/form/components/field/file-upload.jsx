import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
//this is not pretty
import {connect} from 'react-redux'
import {actions} from '#/main/core/data/form/actions.js'

class File extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <fieldset>
        <input
          id={this.props.controlId}
          type="file"
          className="form-control"
          accept={`${this.props.types.join(',')}`}
          ref={input => this.input = input}
          onChange={() => {
            if (this.input.files[0]) {
              const file = this.input.files[0]
              //this.props.value = 'publicFile'
              if (this.props.autoUpload) {
                this.props.uploadFile(file, this.props.uploadUrl, this.props.onChange)
              }
            }
          }
        }
        />
      </fieldset>
    )
  }
}

File.propTypes = {
  controlId: T.string.isRequired,
  value: T.object,
  disabled: T.bool.isRequired,
  types: T.arrayOf(T.string).isRequired,
  max: T.number.isRequired,
  autoUpload: T.bool.isRequired,
  onChange: T.func.isRequired,
  uploadUrl: T.array.isRequired,
  uploadFile: T.func.isRequired
}

File.defaultProps = {
  disabled: false,
  types: [],
  max: 1,
  autoUpload: true,
  onChange: () => {},
  uploadUrl: ['apiv2_file_upload']
}

//this is not pretty
const ConnectedFile = connect(
  () => ({}),
  dispatch => ({uploadFile(file, url, callback) {
    dispatch(actions.uploadFile(file, url, callback))
  }})
)(File)

export {
  ConnectedFile as File
}
